<?php
// One-shot / debug endpoint: inserts missing catalog rows and returns JSON.
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/src/CatalogSeeder.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $database = new Database();
    $added = CatalogSeeder::ensureExtraProducts($database->connection);
    $count = (int) $database->connection->query(
        'SELECT COUNT(*) FROM products_description WHERE is_sold = 0'
    )->fetchColumn();
    echo json_encode([
        'ok' => true,
        'added' => $added,
        'available' => $count,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
}
