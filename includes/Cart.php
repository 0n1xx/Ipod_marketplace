<?php
// Each iPod is a unique physical unit, so the cart is just a set of
// product ids (no quantities) held in the session — nothing is written
// to the database until checkout actually completes.
class Cart {
    private const SESSION_KEY = 'cart_product_ids';

    public static function add(int $productId): void {
        $ids = self::getIds();
        if (!in_array($productId, $ids, true)) {
            $ids[] = $productId;
        }
        $_SESSION[self::SESSION_KEY] = $ids;
    }

    public static function remove(int $productId): void {
        $_SESSION[self::SESSION_KEY] = array_values(
            array_filter(self::getIds(), fn($id) => $id !== $productId)
        );
    }

    public static function clear(): void {
        $_SESSION[self::SESSION_KEY] = [];
    }

    public static function getIds(): array {
        return $_SESSION[self::SESSION_KEY] ?? [];
    }

    public static function count(): int {
        return count(self::getIds());
    }
}
?>
