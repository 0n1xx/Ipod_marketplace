<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    Session::start();
    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }

    $pageTitle = $L['cartTitle'];
    $pageDesc  = $L['cartDesc'];

    $database = new Database();
    $db = new Product($database->connection);

    $cartProducts = $db->getProductsByIds(Cart::getIds());
    $cartProducts = array_values(array_filter($cartProducts, fn($p) => !$p['is_sold']));
    $total = array_sum(array_column($cartProducts, 'price'));
    $itemCount = count($cartProducts);

    $wasCancelled = isset($_GET['cancelled']);
    $hadError = isset($_GET['error']);
    require_once __DIR__ . '/templates/header.php';
?>
<main id="main">
    <div class="container cart-page">
        <div class="page-head page-head--flush">
            <div>
                <p class="eyebrow"><?= $itemCount ?> <?= $itemCount === 1 ? $L['cartItemSingular'] : $L['cartItemPlural'] ?></p>
                <h1 class="display display--lg"><?= $L['cartHeadline'] ?></h1>
            </div>
            <p class="lead"><?= $L['cartDesc'] ?></p>
        </div>

        <?php if ($wasCancelled): ?>
            <div class="alert_failure"><p><?= $L['checkoutCancelled'] ?></p></div>
        <?php endif; ?>
        <?php if ($hadError): ?>
            <div class="alert_failure"><p><?= $L['checkoutError'] ?></p></div>
        <?php endif; ?>

        <?php if (empty($cartProducts)): ?>
            <div class="empty">
                <div class="empty-mark" aria-hidden="true"></div>
                <p><?= $L['cartEmpty'] ?></p>
                <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['backToShopButton'] ?></a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <ul class="cart-list">
                    <?php foreach ($cartProducts as $product): ?>
                        <li class="cart-item">
                            <div class="thumb-lg">
                                <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                            </div>
                            <div class="info">
                                <h2><?= htmlspecialchars($product['name']) ?></h2>
                                <p class="eyebrow"><?= $L['cartItemMeta'] ?></p>
                            </div>
                            <p class="price">$<?= number_format((float)$product['price'], 2) ?></p>
                            <form method="post" action="<?= BASE_URL ?>/cart-remove.php">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                                <button type="submit" class="btn btn--ghost"><?= $L['removeFromCart'] ?></button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <aside class="summary" aria-label="Order summary">
                    <h2><?= $L['cartSummary'] ?></h2>
                    <dl>
                        <div><dt><?= $L['cartItemsLabel'] ?></dt><dd><?= $itemCount ?></dd></div>
                        <div class="total"><dt><?= $L['cartTotal'] ?></dt><dd>$<?= number_format((float)$total, 2) ?> CAD</dd></div>
                    </dl>
                    <form method="post" action="<?= BASE_URL ?>/checkout.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <button type="submit" class="btn btn--blue btn--lg btn--block"><?= $L['proceedToCheckout'] ?></button>
                    </form>
                    <p class="secure-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                        <?= $L['cartSecureNote'] ?>
                    </p>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
