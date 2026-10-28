<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    Session::start();
    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }

    $pageTitle = $L['checkoutSuccessTitle'];
    $pageDesc  = $L['checkoutSuccessTitle'];

    $sessionId = $_GET['session_id'] ?? '';
    $database = new Database();
    $order = null;
    $orderItems = [];
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
            $errorMessage = $L['checkoutWrongAccount'];
        } else {
            $orderModel = new Order($database->connection);
            $order = $orderModel->findByStripeSession($sessionId);

            if (!$order) {
                // Use the product IDs Stripe actually charged for — do NOT
                // filter out sold items here. Payment already succeeded; a race
                // with another buyer must not drop line items from the order.
                $db = new Product($database->connection);
                $productIds = array_filter(array_map(
                    'intval',
                    explode(',', $stripeSession->metadata->product_ids ?? '')
                ));
                $paidProducts = $db->getProductsByIds($productIds);

                $orderId = $orderModel->createFromCart(
                    (int) Session::get('user_id'),
                    $sessionId,
                    $paidProducts
                );

                if ($orderId) {
                    Cart::clear();
                    $order = $orderModel->findByStripeSession($sessionId);
                } else {
                    $errorMessage = $L['checkoutOrderSaveFailed'];
                }
            }

            if ($order) {
                $orders = $orderModel->getOrdersForUser((int) Session::get('user_id'));
                foreach ($orders as $userOrder) {
                    if ((int)$userOrder['id'] === (int)$order['id']) {
                        $orderItems = $userOrder['items'] ?? [];
                        break;
                    }
                }
            }
        }
    }

    require_once __DIR__ . '/templates/header.php';
?>
<main class="checkout-success-page">
    <section class="intro-shop">
        <div>
            <h1><?= $L['checkoutSuccessTitle'] ?></h1>
        </div>
    </section>

    <?php if ($errorMessage): ?>
        <div class="alert_failure"><p><?= htmlspecialchars($errorMessage) ?></p></div>
        <div class="empty-state">
            <p><?= htmlspecialchars($errorMessage) ?></p>
        </div>
    <?php else: ?>
        <div class="alert_success"><p><?= $L['checkoutSuccessText'] ?></p></div>
        <section class="checkout-receipt">
            <p class="checkout-receipt-lead"><?= $L['checkoutSuccessText'] ?></p>
            <?php if (!empty($orderItems)): ?>
                <ul class="checkout-receipt-items">
                    <?php foreach ($orderItems as $item): ?>
                        <li>
                            <?php if (!empty($item['imagePath'])): ?>
                                <img src="<?= htmlspecialchars($item['imagePath']) ?>"
                                     alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                            <?php endif; ?>
                            <span><?= htmlspecialchars($item['name']) ?></span>
                            <strong>$<?= number_format((float)$item['price'], 2) ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php if ($order): ?>
                <p class="cart-total"><?= $L['cartTotal'] ?>: $<?= number_format((float)$order['total_price'], 2) ?></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <div class="buttons" style="justify-content:center; margin: 2rem 0 3rem;">
        <a href="<?= BASE_URL ?>/shop.php" class="button-home-primary"><?= $L['backToShopButton'] ?></a>
    </div>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
