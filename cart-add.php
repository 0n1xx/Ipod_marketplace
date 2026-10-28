<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    Session::start();

    $wantsJson = isset($_POST['ajax'])
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
        || strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') === 0;

    if (!Session::isLoggedIn()) {
        if ($wantsJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'login_required', 'redirect' => BASE_URL . '/login.php']);
            exit();
        }
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify($_POST['csrf_token'] ?? null)) {
        if ($wantsJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'invalid_request']);
            exit();
        }
        header('Location: ' . BASE_URL . '/shop.php');
        exit();
    }

    $productId = (int)($_POST['product_id'] ?? 0);

    $database = new Database();
    $db = new Product($database->connection);
    $product = $db->getProductById($productId);

    $added = false;
    if ($product && !$product['is_sold']) {
        Cart::add($productId);
        $added = true;
    }

    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => $added,
            'count' => Cart::count(),
            'productId' => $productId,
        ]);
        exit();
    }

    $redirect = $_POST['redirect'] ?? 'shop.php';
    if (!preg_match('/^[a-zA-Z0-9_\-]+\.php(\?[a-zA-Z0-9_=&%\-]*)?$/', $redirect)) {
        $redirect = 'shop.php';
    }
    header('Location: ' . BASE_URL . '/' . $redirect);
    exit();
