<?php
    // Creates a Stripe Checkout Session for the current cart and redirects
    // the browser to Stripe-hosted payment UI. There is no HTML here on
    // purpose — success/cancel land back on our own pages.
    require_once __DIR__ . '/includes/bootstrap.php';
    Session::start();

    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }

    // Checkout is a POST from cart.php; GET/forged requests bounce back.
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verify($_POST['csrf_token'] ?? null)) {
        header('Location: ' . BASE_URL . '/cart.php');
        exit();
    }

    if (STRIPE_SECRET_KEY === '') {
        error_log('Stripe checkout failed: STRIPE_SECRET_KEY is not configured');
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
            // Snapshot of what was charged — checkout-success.php reads this
            // instead of trusting the live cart (which may have changed)
            'metadata'            => [
                'product_ids' => implode(',', array_column($cartProducts, 'product_id')),
            ],
        ]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        error_log('Stripe checkout session creation failed: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/cart.php?error=1');
        exit();
    } catch (Throwable $e) {
        error_log('Stripe checkout unexpected failure: ' . $e->getMessage());
        header('Location: ' . BASE_URL . '/cart.php?error=1');
        exit();
    }

    header('Location: ' . $checkoutSession->url);
    exit();
