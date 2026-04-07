<?php
    // Requiring all needed files for this page
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/templates/header.php';
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/admin/CrudProducts.php';

    $pageTitle = $L['shopTitle'];
    $pageDesc  = $L['shopDesc'];

    // Creating a connection to the database to read all products
    $database = new Database();
    $db = new CrudProducts($database->connection);
    // reading all the products
    $products = $db->read();
?>
<main>
    <section class="intro-shop">
        <div>
            <h1><?php echo $L['shopHeroHeadline']; ?></h1>
            <p><?php echo $L['shopHeroSubheadline']; ?></p>
        </div>
    </section>
    <?php if (empty($products)): ?>
        <div class="alert_failure">
            <p><?= $L['productsUnavailable'] ?></p>
        </div>
    <?php else: ?>
    <!-- Creating a section where I'm displaying all products that are available in my database -->
    <section class="product-section">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
            <a href="product.php?id=<?= $product['product_id'] ?>">
                <img src="./<?= htmlspecialchars($product['imagePath']) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>">
                <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
            </a>
            <p class="product-price">
                $<?= number_format((float)$product['price'], 2) ?>
            </p>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <!-- Requiring to include additional piece of content that I insert on some pages -->
    <?php require "./templates/additional_info.php"; ?>
</main>
<?php require_once "./templates/footer.php"; ?>