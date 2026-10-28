<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    Session::start();

    // Adding to the cart requires an account so the order can be tied to
    // someone at checkout
    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify($_POST['csrf_token'] ?? null)) {
        header('Location: ' . BASE_URL . '/shop.php');
        exit();
    }

    $productId = (int)($_POST['product_id'] ?? 0);

    $database = new Database();
    $db = new Product($database->connection);
    $product = $db->getProductById($productId);

    // Only add real, still-available products — never trust the posted id alone
    if ($product && !$product['is_sold']) {
        Cart::add($productId);
    }

    // Only ever redirect to a local page (never an absolute/external URL,
    // to avoid turning this into an open redirect)
    $redirect = $_POST['redirect'] ?? 'shop.php';
    if (!preg_match('/^[a-zA-Z0-9_\-]+\.php(\?[a-zA-Z0-9_=&]*)?$/', $redirect)) {
        $redirect = 'shop.php';
    }
    header('Location: ' . BASE_URL . '/' . $redirect);
    exit();
?>
