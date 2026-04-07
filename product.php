<?php
    // Require all files that needed for this page
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/templates/header.php';
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/admin/CrudProducts.php';

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
    // If product not found, then it will display an error message and close with the footer
    if (!$product) {?>
        <main>
            <div class="alert_failure">
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
            <img src="./<?= htmlspecialchars($product['imagePath']) ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <!-- Displaying the main content of the product -->
        <div class="product-text">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p class="product-price-detail">$<?= number_format((float)$product['price'], 2) ?></p>
            <p class="product-description-detail"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <div class="product-actions">
                <button class="add-to-card" disabled><?php echo $L['addToCartButtonSoon']; ?></button>
                <a href="shop.php" class="back-to-shop"><?php echo $L['backToShopButton']; ?></a>
            </div>
        </div>
    </section>
    <?php require "./templates/additional_info.php"; ?>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>