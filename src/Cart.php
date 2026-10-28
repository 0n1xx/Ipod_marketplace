<?php
// Each iPod is a unique physical unit, so the cart is just a set of
// product ids (no quantities). Session holds a fast cache; for logged-in
// users we also persist to user_cart so redeploys/migrations don't wipe it.
class Cart {
    private const SESSION_KEY = 'cart_product_ids';

    public static function add(int $productId): void {
        $ids = self::getIds();
        if (!in_array($productId, $ids, true)) {
            $ids[] = $productId;
        }
        self::save($ids);
    }

    public static function remove(int $productId): void {
        self::save(array_values(
            array_filter(self::getIds(), fn($id) => $id !== $productId)
        ));
    }

    public static function clear(): void {
        self::save([]);
    }

    public static function getIds(): array {
        if (Session::isLoggedIn()) {
            $ids = self::loadFromDb((int) Session::get('user_id'));
            $_SESSION[self::SESSION_KEY] = $ids;
            return $ids;
        }
        return array_values(array_map('intval', $_SESSION[self::SESSION_KEY] ?? []));
    }

    public static function count(): int {
        return count(self::getIds());
    }

    // Merge guest session cart into the DB cart right after login
    public static function hydrateFromDbOnLogin(int $userId): void {
        $sessionIds = array_values(array_map('intval', $_SESSION[self::SESSION_KEY] ?? []));
        $dbIds = self::loadFromDb($userId);
        $merged = array_values(array_unique(array_merge($dbIds, $sessionIds)));
        self::persistToDb($userId, $merged);
        $_SESSION[self::SESSION_KEY] = $merged;
    }

    private static function save(array $ids): void {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $_SESSION[self::SESSION_KEY] = $ids;
        if (Session::isLoggedIn()) {
            self::persistToDb((int) Session::get('user_id'), $ids);
        }
    }

    private static function db(): ?PDO {
        try {
            $database = new Database();
            return $database->connection;
        } catch (Throwable $e) {
            error_log('Cart: could not open DB: ' . $e->getMessage());
            return null;
        }
    }

    private static function loadFromDb(int $userId): array {
        $pdo = self::db();
        if (!$pdo) {
            return array_values(array_map('intval', $_SESSION[self::SESSION_KEY] ?? []));
        }
        try {
            $stmt = $pdo->prepare(
                'SELECT product_id FROM user_cart WHERE user_id = ? ORDER BY added_at ASC'
            );
            $stmt->execute([$userId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            // Table may not exist yet right after deploy — fall back to session
            error_log('Cart loadFromDb: ' . $e->getMessage());
            return array_values(array_map('intval', $_SESSION[self::SESSION_KEY] ?? []));
        }
    }

    private static function persistToDb(int $userId, array $ids): void {
        $pdo = self::db();
        if (!$pdo) {
            return;
        }
        try {
            $pdo->beginTransaction();
            $del = $pdo->prepare('DELETE FROM user_cart WHERE user_id = ?');
            $del->execute([$userId]);

            if (!empty($ids)) {
                $ins = $pdo->prepare(
                    'INSERT INTO user_cart (user_id, product_id) VALUES (?, ?)'
                );
                foreach ($ids as $productId) {
                    $ins->execute([$userId, $productId]);
                }
            }
            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Cart persistToDb: ' . $e->getMessage());
        }
    }
}
