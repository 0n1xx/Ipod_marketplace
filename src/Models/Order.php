<?php
class Order {
    private $connection;
    public $error = [];

    public function __construct($db) {
        $this->connection = $db;
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

    // Creates the order + its line items, and marks each product sold, all
    // in one transaction — called only after Stripe confirms payment
    public function createFromCart(int $userId, string $stripeSessionId, array $products): int|false {
        try {
            $this->connection->beginTransaction();

            $total = array_sum(array_column($products, 'price'));

            $stmt = $this->connection->prepare(
                "INSERT INTO orders (user_id, stripe_session_id, total_price, status)
                 VALUES (:user_id, :session_id, :total, 'paid')"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':session_id', $stripeSessionId);
            $stmt->bindParam(':total', $total);
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

    // Order history for a user's account page, each with its line items
    public function getOrdersForUser(int $userId): array {
        try {
            $stmt = $this->connection->prepare(
                "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC"
            );
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $itemsStmt = $this->connection->prepare(
                "SELECT oi.price, pd.name, pd.imagePath
                 FROM order_items oi
                 JOIN products_description pd ON pd.product_id = oi.product_id
                 WHERE oi.order_id = :order_id"
            );
            foreach ($orders as &$order) {
                $itemsStmt->execute([':order_id' => $order['id']]);
                $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            return $orders;
        } catch (PDOException $e) {
            $this->error[] = "Database error while fetching order history";
            return [];
        }
    }
}
?>
