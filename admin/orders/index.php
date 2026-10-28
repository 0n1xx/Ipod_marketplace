<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
$query = $_SERVER['QUERY_STRING'] ?? '';
header('Location: ' . BASE_URL . '/admin/orders.php' . ($query !== '' ? '?' . $query : ''), true, 302);
exit;
