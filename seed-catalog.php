<?php
// One-shot / debug endpoint: inserts missing catalog rows and returns JSON.
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/src/CatalogSeeder.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $pdo = $database->connection;
    $added = CatalogSeeder::ensureExtraProducts($pdo);

    $rows = $pdo->query(
        'SELECT product_id, name, price, is_sold, imagePath
         FROM products_description
         ORDER BY product_id'
    )->fetchAll(PDO::FETCH_ASSOC);

    $available = 0;
    foreach ($rows as $row) {
        if ((int) $row['is_sold'] === 0) {
            $available++;
        }
    }

    echo json_encode([
        'ok' => true,
        'added' => $added,
        'available' => $available,
        'total' => count($rows),
        'products' => $rows,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}
