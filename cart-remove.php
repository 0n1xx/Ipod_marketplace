<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    Session::start();

    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify($_POST['csrf_token'] ?? null)) {
        header('Location: ' . BASE_URL . '/cart.php');
        exit();
    }

    Cart::remove((int)($_POST['product_id'] ?? 0));

    header('Location: ' . BASE_URL . '/cart.php');
    exit();
?>
