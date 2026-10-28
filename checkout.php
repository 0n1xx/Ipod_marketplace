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

    if (STRIPE_SECRET_KEY === '') {
        $msg = 'STRIPE_SECRET_KEY is not configured';
        error_log('Stripe checkout failed: ' . $msg);
        Session::set('checkout_error_raw', $msg);
        header('Location: ' . BASE_URL . '/cart.php?error=1');
        exit();
    }

    $database = new Database();
    $db = new Product($database->connection);

    // Re-check availability right before charging — a product could have
    // sold out between being added to the cart and checking out
    $cartProducts = $db->getProductsByIds(Cart::getIds());
    $cartProducts = array_values(array_filter($cartProducts, fn($p) => !$p['is_sold']));

    if (empty($cartProducts)) {
        header('Location: ' . BASE_URL . '/cart.php');
        exit();
    }

    $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . BASE_URL;

    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    $lineItems = array_map(function ($product) {
        return [
            'price_data' => [
                'currency'     => 'cad',
                'product_data' => ['name' => $product['name']],
                // Stripe expects the smallest currency unit (cents)
                'unit_amount'  => (int) round($product['price'] * 100),
            ],
            'quantity' => 1,
        ];
    }, $cartProducts);

    try {
        $checkoutSession = \Stripe\Checkout\Session::create([
            'mode'                => 'payment',
            'line_items'          => $lineItems,
            'success_url'         => $baseUrl . '/checkout-success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'          => $baseUrl . '/cart.php?cancelled=1',
            'client_reference_id' => (string) Session::get('user_id'),
            // Recorded here so checkout-success.php can reconstruct exactly what
            // was paid for, instead of trusting whatever is in the cart by then
            // (which may have changed since this session was created)
            'metadata'            => [
                'product_ids' => implode(',', array_column($cartProducts, 'product_id')),
            ],
        ]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $msg = $e->getMessage();
        error_log('Stripe checkout session creation failed: ' . $msg);
        Session::set('checkout_error_raw', $msg);
        header('Location: ' . BASE_URL . '/cart.php?error=1');
        exit();
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('Stripe checkout unexpected failure: ' . $msg);
        Session::set('checkout_error_raw', $msg);
        header('Location: ' . BASE_URL . '/cart.php?error=1');
        exit();
    }

    header('Location: ' . $checkoutSession->url);
    exit();
