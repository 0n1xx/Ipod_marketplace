<?php
    // Stripe redirects here after a successful payment with ?session_id=...
    // We verify the session server-side, record the order once, clear the cart,
    // and show a receipt. Reloading this page must not create a duplicate order.
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
            // (e.g. a leaked/shared success URL)
            $errorMessage = $L['checkoutWrongAccount'];
        } else {
            $orderModel = new Order($database->connection);
            $order = $orderModel->findByStripeSession($sessionId);

            $isNewOrder = false;
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
                    $paidProducts,
                    Order::shippingFromStripe($stripeSession)
                );

                if ($orderId) {
                    Cart::clear();
                    $order = $orderModel->findByStripeSession($sessionId);
                    $isNewOrder = true;
                } else {
                    $errorMessage = $L['checkoutOrderSaveFailed'];
                }
            }

            if ($order) {
                $orderItems = $orderModel->getItemsForOrder((int)$order['id']);
                $order['status'] = Order::normalizeStatus($order['status'] ?? 'paid');
            }

            // Receipt email only on the first successful save (not on refresh)
            if ($isNewOrder && $order && !empty($orderItems)) {
                $userModel = new User($database->connection);
                $customer = $userModel->findById((int) Session::get('user_id'));
                $email = $customer['email'] ?? '';
                if ($email !== '') {
                    Mailer::send(
                        $email,
                        sprintf($L['receiptEmailSubject'], (int)$order['id']),
                        EmailTemplates::orderReceipt(
                            $order,
                            $orderItems,
                            $L,
                            (string)($customer['name'] ?? '')
                        )
                    );
                }
            }
        }
    }

    require_once __DIR__ . '/templates/header.php';
?>
<main id="main">
    <div class="container success-layout">
        <div class="success-copy">
            <div class="check-circle" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
            </div>
            <h1 class="display display--lg"><?= $L['checkoutSuccessTitle'] ?></h1>

            <?php if ($errorMessage): ?>
                <div class="alert_failure"><p><?= htmlspecialchars($errorMessage) ?></p></div>
            <?php else: ?>
                <div class="alert_success"><p><?= $L['checkoutSuccessText'] ?></p></div>
            <?php endif; ?>

            <div class="btn-row">
                <a href="<?= BASE_URL ?>/shop.php" class="btn btn--dark"><?= $L['backToShopButton'] ?></a>
            </div>
        </div>

        <?php if (!$errorMessage && $order): ?>
            <aside class="receipt" aria-label="Receipt">
                <div class="receipt-head">
                    <span>QuébecIpod</span>
                    <span>#<?= (int)$order['id'] ?></span>
                </div>
                <?php if (!empty($orderItems)): ?>
                    <ul>
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
                <div class="receipt-total">
                    <span><?= $L['cartTotal'] ?></span>
                    <span>$<?= number_format((float)$order['total_price'], 2) ?></span>
                </div>
                <?php
                    $orderAddressClass = 'order-address--receipt';
                    require __DIR__ . '/templates/order-address.php';
                ?>
            </aside>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
