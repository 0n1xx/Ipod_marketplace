<?php
/**
 * Inserts the extra iPod catalog rows if they are not already present.
 * Usage (from project root, with DB env vars loaded):
 *   php scripts/seed_extra_products.php
 *
 * On Railway the same seeder also runs automatically the first time
 * someone opens shop.php after deploy.
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
