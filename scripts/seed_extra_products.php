<?php
/**
 * Inserts the extra iPod catalog rows if they are not already present.
 * Usage (from project root, with DB env vars loaded):
 *   php scripts/seed_extra_products.php
 *
 * On Railway, shop.php also calls CatalogSeeder::ensureIfSparse() so an
 * empty DB is filled on first visit; this script is for manual/CLI seeds.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

try {
    $database = new Database();
    $added = CatalogSeeder::ensureExtraProducts($database->connection);
    echo $added > 0
        ? "Done. Added {$added} products.\n"
        : "Done. Catalog already up to date.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Seed failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
