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
        } else {
            $orderModel = new Order($database->connection);

            // Avoid recording the same order twice if this page is reloaded
            $order = $orderModel->findByStripeSession($sessionId);

            if (!$order) {
                $db = new CrudProducts($database->connection);
                $cartProducts = $db->getProductsByIds(Cart::getIds());
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
