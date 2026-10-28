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
    <div class="container empty empty--photo" style="--empty-img:url('<?= BASE_URL ?>/images/workshop.jpg')">
        <p><?= $L['productNotFound'] ?></p>
        <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['backToShopButton'] ?></a>
    </div>
</main>
<?php
        require_once __DIR__ . '/templates/footer.php';
        exit;
    endif;

    $inCart = in_array((int)$product['product_id'], Cart::getIds(), true);
    $slides = array_merge(
        [['url' => $product['imagePath'], 'alt' => $product['name']]],
        array_map(
            static fn($image) => ['url' => $image['image_url'], 'alt' => $product['name']],
            $galleryImages
        )
    );
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
        <div class="gallery" data-gallery>
            <div class="gallery-stage">
                <?php if ($product['is_sold']): ?>
                    <span class="sold-badge"><?= $L['soldBadge'] ?></span>
                <?php endif; ?>
                <div class="gallery-track" data-gallery-track>
                    <?php foreach ($slides as $i => $slide): ?>
                        <figure class="gallery-slide" data-gallery-slide>
                            <img src="<?= htmlspecialchars($slide['url']) ?>"
                                 alt="<?= htmlspecialchars($slide['alt']) ?>"
                                 width="900" height="900"
                                 <?= $i === 0 ? 'id="main-product-image"' : 'loading="lazy"' ?>>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <?php if (count($slides) > 1): ?>
                    <button type="button" class="gallery-nav gallery-nav--prev" data-gallery-prev aria-label="<?= $L['galleryPrev'] ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
                    </button>
                    <button type="button" class="gallery-nav gallery-nav--next" data-gallery-next aria-label="<?= $L['galleryNext'] ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                    </button>
                <?php endif; ?>
            </div>
            <?php if (count($slides) > 1): ?>
                <div class="thumbs" role="list">
                    <?php foreach ($slides as $i => $slide): ?>
                        <button type="button" class="thumb" data-gallery-thumb="<?= $i ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                            <img src="<?= htmlspecialchars($slide['url']) ?>" alt="" loading="lazy">
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
                    <form method="post" action="<?= BASE_URL ?>/cart-add.php" data-add-to-cart data-product-image="<?= htmlspecialchars($product['imagePath']) ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                        <input type="hidden" name="redirect" value="product.php?id=<?= (int)$product['product_id'] ?>&lang=<?= $lang ?>">
                        <button type="submit" class="btn btn--dark btn--lg btn--block btn--arrow">
                            <?= $L['addToCartButton'] ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--outline btn--block"><?= $L['backToShopButton'] ?></a>
            </div>
        </aside>
    </section>

    <?php require __DIR__ . '/templates/additional_info.php'; ?>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
