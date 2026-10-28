<?php
// Admin-only catalog seed / debug endpoint (JSON).
require_once __DIR__ . '/includes/bootstrap.php';

Session::start();
header('Content-Type: application/json; charset=utf-8');

if (!Session::isLoggedIn() || Session::get('is_admin') != 1) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Admin only']);
    exit;
}

try {
    $database = new Database();
    $pdo = $database->connection;
    $added = CatalogSeeder::ensureExtraProducts($pdo);
    $resynced = CatalogSeeder::resyncSoldFromOrders($pdo);

    $rows = $pdo->query(
        'SELECT product_id, name, price, is_sold, imagePath
         FROM products_description
         ORDER BY product_id'
    )->fetchAll(PDO::FETCH_ASSOC);

    $available = count(array_filter($rows, fn($row) => (int)$row['is_sold'] === 0));

    echo json_encode([
        'ok' => true,
        'added' => $added,
        'resyncedSold' => $resynced,
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
