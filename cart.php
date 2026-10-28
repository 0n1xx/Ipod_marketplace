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

    // Products may have sold out (someone else bought them) or been removed
    // since they were added to the cart — only show what's still available
    $cartProducts = $db->getProductsByIds(Cart::getIds());
    $cartProducts = array_values(array_filter($cartProducts, fn($p) => !$p['is_sold']));
    $total = array_sum(array_column($cartProducts, 'price'));

    $wasCancelled = isset($_GET['cancelled']);
    $hadError = isset($_GET['error']);

    require_once __DIR__ . '/templates/header.php';
?>
<main class="cart-page">
    <section class="intro-shop">
        <div>
            <h1><?= $L['cartHeadline'] ?></h1>
            <p><?= $L['cartDesc'] ?></p>
        </div>
    </section>

    <?php if ($wasCancelled): ?>
        <div class="alert_failure"><p><?= $L['checkoutCancelled'] ?></p></div>
    <?php endif; ?>
    <?php if ($hadError): ?>
        <div class="alert_failure"><p><?= $L['checkoutError'] ?></p></div>
    <?php endif; ?>

    <?php if (empty($cartProducts)): ?>
        <div class="empty-state">
            <p><?= $L['cartEmpty'] ?></p>
        </div>
        <div class="buttons" style="justify-content:center; margin-bottom: 3rem;">
            <a href="<?= BASE_URL ?>/shop.php" class="button-home-primary"><?= $L['backToShopButton'] ?></a>
        </div>
    <?php else: ?>
        <div class="cart-shell">
            <section class="cart-items">
                <?php foreach ($cartProducts as $product): ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        <div class="cart-item-details">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-price">$<?= number_format((float)$product['price'], 2) ?></p>
                        </div>
                        <form method="post" action="<?= BASE_URL ?>/cart-remove.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="btn-delete"><?= $L['removeFromCart'] ?></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </section>
            <section class="cart-summary">
                <p class="cart-total"><?= $L['cartTotal'] ?>: $<?= number_format((float)$total, 2) ?></p>
                <form method="post" action="<?= BASE_URL ?>/checkout.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <button type="submit" class="button-home-primary"><?= $L['proceedToCheckout'] ?></button>
                </form>
            </section>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
