<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/admin/_helpers.php';

    adminRequireAuth();

    $pageTitle = $L['soldTitle'];
    $pageDesc  = $L['soldDesc'];

    $database = new Database();
    // Sold archive always reflects DB is_sold=1; resync from order_items first
    CatalogSeeder::resyncSoldFromOrders($database->connection);

    $db = new Product($database->connection);

    $searchQuery = trim($_GET['q'] ?? '');
    $allowedSorts = ['newest', 'price_asc', 'price_desc'];
    $sort = in_array($_GET['sort'] ?? '', $allowedSorts, true) ? $_GET['sort'] : 'newest';

    $products = $db->search($searchQuery, $sort, 'sold') ?: [];
    $countLabel = count($products) . ' ' . $L['soldCountLabel'];

    require_once __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container page-head">
        <div>
            <p class="eyebrow"><?= $L['navSold'] ?> · <span><?= htmlspecialchars($countLabel) ?></span></p>
            <h1 class="display display--lg"><?= $L['soldHeadline'] ?></h1>
        </div>
        <p class="lead"><?= $L['soldSubheadline'] ?></p>
    </section>

    <section class="shop-bar" aria-label="Search and sort">
        <form class="container" method="get" action="<?= BASE_URL ?>/sold.php" role="search">
            <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
            <label for="sold-q" class="sr-only"><?= $L['searchPlaceholder'] ?></label>
            <div class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input id="sold-q" type="search" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="<?= $L['searchPlaceholder'] ?>">
            </div>
            <label for="sold-sort" class="eyebrow"><?= $L['sortLabel'] ?></label>
            <select id="sold-sort" name="sort" class="select">
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
                <p><?= $L['soldEmpty'] ?></p>
                <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['backToShopButton'] ?></a>
            </div>
        <?php else: ?>
            <?php
                $showSoldBadge = true;
                require __DIR__ . '/templates/product-grid.php';
            ?>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
