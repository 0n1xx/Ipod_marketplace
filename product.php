<?php
    // Require all files that needed for this page
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/templates/header.php';

    $pageTitle = $L['shopTitle'];
    $pageDesc  = $L['shopDesc'];

    // Getting a specific id of one product
    $productId = (int)$_GET['id'];

    // Connecting to the database
    $database = new Database();
    $db = new CrudProducts($database->connection);

    // Going over all available products and stopping when the needed one is found
    $product = null;
    foreach ($db->read() as $p) {
        if ((int)$p['product_id'] === $productId) {
            $product = $p;
            break;
        }
    }
    // The primary image plus any extra gallery photos, for the thumbnail strip
    $galleryImages = $product ? $db->getGalleryImages($product['product_id']) : [];

    // If product not found, then it will display an error message and close with the footer
    if (!$product) {?>
        <main>
            <div class="empty-state">
                <p><?php echo $L['productNotFound']; ?></p>
            </div>
        </main>
        <?php
        require_once __DIR__ . '/templates/footer.php';
        exit;
    }
?>
<main class="individual-product">
    <!-- Applying the same class that has already been used on the shop page -->
    <section class="intro-shop">
        <h1><?php echo $L['shopHeroHeadline']; ?></h1>
        <p><?php echo $L['shopHeroSubheadline']; ?></p>
    </section>
    <section class="product-detail">
        <!-- Displaying a product image -->
        <div class="product-image">
            <?php if ($product['is_sold']): ?>
                <span class="sold-badge"><?= $L['soldBadge'] ?></span>
            <?php endif; ?>
            <img id="main-product-image" src="<?= htmlspecialchars($product['imagePath']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>">
            <?php if (!empty($galleryImages)): ?>
                <!-- Thumbnail strip: clicking a thumbnail swaps #main-product-image's src (see custom-js.js) -->
                <div class="product-thumbnails">
                    <button type="button" class="product-thumbnail active"
                            data-image="<?= htmlspecialchars($product['imagePath']) ?>">
                        <img src="<?= htmlspecialchars($product['imagePath']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                    </button>
                    <?php foreach ($galleryImages as $image): ?>
                        <button type="button" class="product-thumbnail"
                                data-image="<?= htmlspecialchars($image['image_url']) ?>">
                            <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <!-- Displaying the main content of the product -->
        <div class="product-text">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p class="product-price-detail">$<?= number_format((float)$product['price'], 2) ?></p>
            <p class="product-description-detail"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <div class="product-actions">
                <?php if ($product['is_sold']): ?>
                    <button class="add-to-card" disabled><?php echo $L['soldBadge']; ?></button>
                <?php elseif (in_array($product['product_id'], Cart::getIds(), true)): ?>
                    <a href="cart.php" class="add-to-card"><?= $L['viewCart'] ?></a>
                <?php else: ?>
                    <form method="post" action="cart-add.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="redirect" value="product.php?id=<?= $product['product_id'] ?>">
                        <button type="submit" class="add-to-card"><?php echo $L['addToCartButton']; ?></button>
                    </form>
                <?php endif; ?>
                <a href="shop.php" class="back-to-shop"><?php echo $L['backToShopButton']; ?></a>
            </div>
        </div>
    </section>
    <?php require "./templates/additional_info.php"; ?>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>