<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/templates/header.php';

    $pageTitle = $L['checkoutSuccessTitle'];
    $pageDesc  = $L['checkoutSuccessTitle'];

    Session::start();
    if (!Session::isLoggedIn()) {
        header("Location: login.php");
        exit();
    }

    $sessionId = $_GET['session_id'] ?? '';
    $database = new Database();
    $order = null;
    $errorMessage = null;

    if ($sessionId === '') {
        $errorMessage = $L['checkoutNoSession'];
    } else {
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        try {
            $stripeSession = \Stripe\Checkout\Session::retrieve($sessionId);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $stripeSession = null;
        }

        if (!$stripeSession || $stripeSession->payment_status !== 'paid') {
            $errorMessage = $L['checkoutNotPaid'];
        } elseif ((string) $stripeSession->client_reference_id !== (string) Session::get('user_id')) {
            // Stop one account from claiming another account's paid session
            // (e.g. a leaked/shared success URL)
            $errorMessage = $L['checkoutWrongAccount'];
        } else {
            $orderModel = new Order($database->connection);

            // Avoid recording the same order twice if this page is reloaded
            $order = $orderModel->findByStripeSession($sessionId);

            if (!$order) {
                // Use the product IDs Stripe actually charged for (stored as
                // metadata at checkout time), not whatever is in the cart now —
                // the cart may have changed since this session was created
                $db = new CrudProducts($database->connection);
                $productIds = array_filter(array_map(
                    'intval',
                    explode(',', $stripeSession->metadata->product_ids ?? '')
                ));
                $cartProducts = $db->getProductsByIds($productIds);
                $cartProducts = array_values(array_filter($cartProducts, fn($p) => !$p['is_sold']));

                $orderId = $orderModel->createFromCart(
                    (int) Session::get('user_id'),
                    $sessionId,
                    $cartProducts
                );

                if ($orderId) {
                    Cart::clear();
                    $order = $orderModel->findByStripeSession($sessionId);
                } else {
                    $errorMessage = $L['checkoutOrderSaveFailed'];
                }
            }
        }
    }
?>
<main>
    <section class="intro-shop">
        <div>
            <h1><?= $L['checkoutSuccessTitle'] ?></h1>
        </div>
    </section>

    <?php if ($errorMessage): ?>
        <div class="alert_failure"><p><?= htmlspecialchars($errorMessage) ?></p></div>
    <?php else: ?>
        <div class="alert_success"><p><?= $L['checkoutSuccessText'] ?></p></div>
        <?php if ($order): ?>
            <section class="cart-summary">
                <p class="cart-total"><?= $L['cartTotal'] ?>: $<?= number_format((float)$order['total_price'], 2) ?></p>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <div class="buttons" style="justify-content:center; margin: 2rem 0 3rem;">
        <a href="shop.php" class="button-home-primary"><?= $L['backToShopButton'] ?></a>
    </div>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
