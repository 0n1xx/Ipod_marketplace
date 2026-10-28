<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    $pageTitle = $L['shopTitle'];
    $pageDesc  = $L['shopDesc'];

    $database = new Database();
    $db = new Product($database->connection);

    $searchQuery = trim($_GET['q'] ?? '');
    $allowedSorts = ['newest', 'price_asc', 'price_desc'];
    $sort = in_array($_GET['sort'] ?? '', $allowedSorts, true) ? $_GET['sort'] : 'newest';

    $products = $db->search($searchQuery, $sort, 'available') ?: [];
    $countLabel = count($products) . ' ' . $L['shopCountLabel'];

    require_once __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container page-head">
        <div>
            <p class="eyebrow"><?= $L['navShop'] ?> · <span><?= htmlspecialchars($countLabel) ?></span></p>
            <h1 class="display display--lg"><?= $L['shopHeroHeadline'] ?></h1>
        </div>
        <p class="lead"><?= $L['shopHeroSubheadline'] ?></p>
    </section>

    <section class="shop-bar" aria-label="Search and sort">
        <form class="container" method="get" action="<?= BASE_URL ?>/shop.php" role="search">
            <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
            <label for="shop-q" class="sr-only"><?= $L['searchPlaceholder'] ?></label>
            <div class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input id="shop-q" type="search" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="<?= $L['searchPlaceholder'] ?>">
            </div>
            <label for="shop-sort" class="eyebrow"><?= $L['sortLabel'] ?></label>
            <select id="shop-sort" name="sort" class="select">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>><?= $L['sortNewest'] ?></option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>><?= $L['sortPriceAsc'] ?></option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>><?= $L['sortPriceDesc'] ?></option>
            </select>
            <button type="submit" class="btn btn--dark"><?= $L['searchButton'] ?></button>
        </form>
    </section>

    <section class="container section section--tight-top">
        <?php if (empty($products)): ?>
            <div class="empty">
                <p><?= $searchQuery !== '' ? $L['noSearchResults'] : $L['productsUnavailable'] ?></p>
                <?php if ($searchQuery !== ''): ?>
                    <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['clearSearch'] ?></a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <ul class="product-grid">
                <?php foreach ($products as $product): ?>
                    <li class="product-card">
                        <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['product_id'] ?>&lang=<?= $lang ?>">
                            <div class="tile">
                                <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                            </div>
                            <div class="meta">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="price">$<?= number_format((float)$product['price'], 2) ?></p>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <?php require __DIR__ . '/templates/additional_info.php'; ?>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
