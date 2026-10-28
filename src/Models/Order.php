<?php
class Order {
    public const STATUSES = ['paid', 'shipped', 'delivered'];

    private $connection;
    public $error = [];

    public function __construct($db) {
        $this->connection = $db;
        $this->ensureShippingColumns();
    }

    public static function isValidStatus(string $status): bool {
        return in_array($status, self::STATUSES, true);
    }

    // Idempotent schema nudge for deploys that predate shipping columns
    private function ensureShippingColumns(): void {
        static $ready = false;
        if ($ready) {
            return;
        }
        $ready = true;
        try {
            $col = $this->connection->query("SHOW COLUMNS FROM orders LIKE 'shipping_line1'")->fetch();
            if ($col) {
                return;
            }
            $this->connection->exec(
                'ALTER TABLE orders
                    ADD COLUMN shipping_name VARCHAR(150) NULL,
                    ADD COLUMN shipping_line1 VARCHAR(255) NULL,
                    ADD COLUMN shipping_line2 VARCHAR(255) NULL,
                    ADD COLUMN shipping_city VARCHAR(100) NULL,
                    ADD COLUMN shipping_state VARCHAR(100) NULL,
                    ADD COLUMN shipping_postal VARCHAR(30) NULL,
                    ADD COLUMN shipping_country VARCHAR(2) NULL'
            );
        } catch (PDOException $e) {
            error_log('Order shipping migration skipped: ' . $e->getMessage());
        }
    }

    // Normalize Stripe shipping_details / shipping into DB-ready fields
    public static function shippingFromStripe($session): array {
        $details = $session->shipping_details ?? $session->shipping ?? null;
        if (!$details && isset($session->collected_information)) {
            $details = $session->collected_information->shipping_details ?? null;
        }
        if (!$details) {
            return [];
        }
        $address = $details->address ?? null;
        if (!$address) {
            return [];
        }
        return array_filter([
            'shipping_name'    => trim((string)($details->name ?? '')),
            'shipping_line1'   => trim((string)($address->line1 ?? '')),
            'shipping_line2'   => trim((string)($address->line2 ?? '')),
            'shipping_city'    => trim((string)($address->city ?? '')),
            'shipping_state'   => trim((string)($address->state ?? '')),
            'shipping_postal'  => trim((string)($address->postal_code ?? '')),
            'shipping_country' => strtoupper(trim((string)($address->country ?? ''))),
        ], static fn($v) => $v !== '');
    }

    public static function formatShippingAddress(array $order): string {
        $lines = [];
        if (!empty($order['shipping_name'])) {
            $lines[] = (string)$order['shipping_name'];
        }
        if (!empty($order['shipping_line1'])) {
            $lines[] = (string)$order['shipping_line1'];
        }
        if (!empty($order['shipping_line2'])) {
            $lines[] = (string)$order['shipping_line2'];
        }
        $cityLine = trim(implode(', ', array_filter([
            $order['shipping_city'] ?? '',
            $order['shipping_state'] ?? '',
            $order['shipping_postal'] ?? '',
        ])));
        if ($cityLine !== '') {
            $lines[] = $cityLine;
        }
        if (!empty($order['shipping_country'])) {
            $lines[] = (string)$order['shipping_country'];
        }
        return implode("\n", $lines);
    }

    public static function hasShippingAddress(array $order): bool {
        return self::formatShippingAddress($order) !== '';
    }

    // Returns the existing order for a Stripe session, or null if none was
    // recorded yet — lets checkout-success.php be safely re-visited
    // (e.g. a page refresh) without creating duplicate orders
    public function findByStripeSession(string $sessionId) {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM orders WHERE stripe_session_id = :session_id LIMIT 1"
            );
            $stmt->bindParam(':session_id', $sessionId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $this->error[] = "Database error while looking up order";
            return null;
        }
    }

    public function findById(int $orderId): ?array {
        try {
            $stmt = $this->connection->prepare(
                'SELECT * FROM orders WHERE id = :id LIMIT 1'
            );
            $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            return $order ?: null;
        } catch (PDOException $e) {
            $this->error[] = 'Database error while looking up order';
            return null;
        }
    }

    public function getItemsForOrder(int $orderId): array {
        try {
            $stmt = $this->connection->prepare(
                'SELECT oi.price, pd.name, pd.imagePath
                 FROM order_items oi
                 JOIN products_description pd ON pd.product_id = oi.product_id
                 WHERE oi.order_id = :order_id'
            );
            $stmt->bindValue(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->error[] = 'Database error while fetching order items';
            return [];
        }
    }

    // Creates the order + its line items, and marks each product sold, all
    // in one transaction — called only after Stripe confirms payment
    public function createFromCart(int $userId, string $stripeSessionId, array $products, array $shipping = []): int|false {
        try {
            $this->connection->beginTransaction();

            $total = array_sum(array_column($products, 'price'));

            $stmt = $this->connection->prepare(
                "INSERT INTO orders (
                    user_id, stripe_session_id, total_price, status,
                    shipping_name, shipping_line1, shipping_line2,
                    shipping_city, shipping_state, shipping_postal, shipping_country
                 ) VALUES (
                    :user_id, :session_id, :total, 'paid',
                    :shipping_name, :shipping_line1, :shipping_line2,
                    :shipping_city, :shipping_state, :shipping_postal, :shipping_country
                 )"
            );
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':session_id', $stripeSessionId);
            $stmt->bindValue(':total', $total);
            $stmt->bindValue(':shipping_name', $shipping['shipping_name'] ?? null);
            $stmt->bindValue(':shipping_line1', $shipping['shipping_line1'] ?? null);
            $stmt->bindValue(':shipping_line2', $shipping['shipping_line2'] ?? null);
            $stmt->bindValue(':shipping_city', $shipping['shipping_city'] ?? null);
            $stmt->bindValue(':shipping_state', $shipping['shipping_state'] ?? null);
            $stmt->bindValue(':shipping_postal', $shipping['shipping_postal'] ?? null);
            $stmt->bindValue(':shipping_country', $shipping['shipping_country'] ?? null);
            $stmt->execute();
            $orderId = (int)$this->connection->lastInsertId();

            $itemStmt = $this->connection->prepare(
                "INSERT INTO order_items (order_id, product_id, price) VALUES (:order_id, :product_id, :price)"
            );
            $soldStmt = $this->connection->prepare(
                "UPDATE products_description SET is_sold = 1 WHERE product_id = :product_id"
            );

            foreach ($products as $product) {
                $itemStmt->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => $product['product_id'],
                    ':price'      => $product['price'],
                ]);
                $soldStmt->execute([':product_id' => $product['product_id']]);
            }

            $this->connection->commit();
            return $orderId;
        } catch (PDOException $e) {
            $this->connection->rollBack();
            $this->error[] = "Database error while recording the order";
            return false;
        }
    }

    public function updateStatus(int $orderId, string $status): bool {
        if (!self::isValidStatus($status)) {
            $this->error[] = 'Invalid order status.';
            return false;
        }
        try {
            $stmt = $this->connection->prepare(
                'UPDATE orders SET status = :status WHERE id = :id'
            );
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() >= 0;
        } catch (PDOException $e) {
            $this->error[] = 'Database error while updating order status';
            return false;
        }
    }

    // Order history for a user's account page, each with its line items
    public function getOrdersForUser(int $userId): array {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as &$order) {
                $order['items'] = $this->getItemsForOrder((int)$order['id']);
                $order['status'] = self::normalizeStatus($order['status'] ?? 'paid');
            }
            return $orders;
        } catch (PDOException $e) {
            $this->error[] = "Database error while fetching order history";
            return [];
        }
    }

    // Admin monitor: all orders with customer + items
    public function getAllOrders(): array {
        try {
            $stmt = $this->connection->query(
                'SELECT o.*, u.name AS customer_name, u.email AS customer_email
                 FROM orders o
                 JOIN users u ON u.id = o.user_id
                 ORDER BY o.created_at DESC'
            );
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($orders as &$order) {
                $order['items'] = $this->getItemsForOrder((int)$order['id']);
                $order['status'] = self::normalizeStatus($order['status'] ?? 'paid');
            }
            return $orders;
        } catch (PDOException $e) {
            $this->error[] = 'Database error while fetching orders';
            return [];
        }
    }

    public static function normalizeStatus(?string $status): string {
        $status = strtolower(trim((string)$status));
        if ($status === 'complete' || $status === 'completed' || $status === 'pending' || $status === '') {
            return 'paid';
        }
        return self::isValidStatus($status) ? $status : 'paid';
    }
}
