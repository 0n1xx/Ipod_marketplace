<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    Session::start();

    $productId = (int)($_GET['id'] ?? 0);
    $database = new Database();
    $db = new Product($database->connection);
    $product = $productId > 0 ? $db->getProductById($productId) : null;
    $galleryImages = $product ? $db->getGalleryImages($product['product_id']) : [];

    $pageTitle = $product ? $product['name'] : $L['productNotFound'];
    $pageDesc  = $product ? $product['description'] : $L['shopDesc'];

    require_once __DIR__ . '/templates/header.php';

    if (!$product):
?>
<main id="main">
    <div class="container empty">
        <div class="empty-mark" aria-hidden="true"></div>
        <p><?= $L['productNotFound'] ?></p>
        <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['backToShopButton'] ?></a>
    </div>
</main>
<?php
        require_once __DIR__ . '/templates/footer.php';
        exit;
    endif;

    $inCart = in_array((int)$product['product_id'], Cart::getIds(), true);
?>
<main id="main">
    <nav class="container breadcrumb" aria-label="Breadcrumb">
        <ol>
            <li><a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>"><?= $L['navShop'] ?></a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <section class="container product-layout">
        <div class="gallery">
            <div class="gallery-main">
                <?php if ($product['is_sold']): ?>
                    <span class="sold-badge"><?= $L['soldBadge'] ?></span>
                <?php endif; ?>
                <img id="main-product-image" src="<?= htmlspecialchars($product['imagePath']) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>" width="900" height="900">
            </div>
            <?php if (!empty($galleryImages)): ?>
                <div class="thumbs" role="list">
                    <button type="button" class="thumb" data-image="<?= htmlspecialchars($product['imagePath']) ?>" aria-pressed="true">
                        <img src="<?= htmlspecialchars($product['imagePath']) ?>" alt="" loading="lazy">
                    </button>
                    <?php foreach ($galleryImages as $image): ?>
                        <button type="button" class="thumb" data-image="<?= htmlspecialchars($image['image_url']) ?>" aria-pressed="false">
                            <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="buybox">
            <div class="buybox-head">
                <h1 class="display display--sm"><?= htmlspecialchars($product['name']) ?></h1>
                <p class="price-lg">$<?= number_format((float)$product['price'], 2) ?> CAD</p>
                <p class="desc"><?= htmlspecialchars($product['description']) ?></p>
            </div>
            <div class="actions">
                <?php if ($product['is_sold']): ?>
                    <button type="button" class="btn btn--dark btn--lg btn--block" disabled><?= $L['soldBadge'] ?></button>
                <?php elseif ($inCart): ?>
                    <a href="<?= BASE_URL ?>/cart.php?lang=<?= $lang ?>" class="btn btn--blue btn--lg btn--block"><?= $L['viewCart'] ?></a>
                <?php else: ?>
                    <form method="post" action="<?= BASE_URL ?>/cart-add.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                        <input type="hidden" name="redirect" value="product.php?id=<?= (int)$product['product_id'] ?>">
                        <button type="submit" class="btn btn--dark btn--lg btn--block"><?= $L['addToCartButton'] ?></button>
                    </form>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--outline btn--block"><?= $L['backToShopButton'] ?></a>
            </div>
        </aside>
    </section>

    <?php require __DIR__ . '/templates/additional_info.php'; ?>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
