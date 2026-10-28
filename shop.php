<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    $pageTitle = $L['shopTitle'];
    $pageDesc  = $L['shopDesc'];

    $database = new Database();
    CatalogSeeder::ensureIfSparse($database->connection);
    $db = new Product($database->connection);

    $searchQuery = trim($_GET['q'] ?? '');
    $allowedSorts = ['newest', 'price_asc', 'price_desc'];
    $sort = in_array($_GET['sort'] ?? '', $allowedSorts, true) ? $_GET['sort'] : 'newest';

    $allowedModels = ['classic', 'mini', 'shuffle'];
    $allowedColors = ['blue', 'green', 'silver', 'red', 'purple', 'black', 'pink', 'gold', 'orange', 'white', 'teal', 'lavender'];
    $model = in_array(strtolower($_GET['model'] ?? ''), $allowedModels, true) ? strtolower($_GET['model']) : '';
    $color = in_array(strtolower($_GET['color'] ?? ''), $allowedColors, true) ? strtolower($_GET['color']) : '';

    $products = $db->search($searchQuery, $sort, 'available') ?: [];
    if ($model !== '') {
        $products = array_values(array_filter(
            $products,
            fn($p) => stripos($p['name'], $model) !== false
        ));
    }
    if ($color !== '') {
        $products = array_values(array_filter(
            $products,
            fn($p) => stripos($p['name'], $color) !== false
                || ($color === 'gold' && (stripos($p['name'], 'gold') !== false || stripos($p['name'], 'rose') !== false))
                || ($color === 'black' && stripos($p['name'], 'space') !== false)
        ));
    }

    $countLabel = count($products) . ' ' . $L['shopCountLabel'];
    $chipBase = ['lang' => $lang, 'q' => $searchQuery, 'sort' => $sort];

    require_once __DIR__ . '/templates/header.php';

    $chipUrl = static function (array $overrides) use ($chipBase, $model, $color): string {
        $params = array_merge($chipBase, [
            'model' => $model,
            'color' => $color,
        ], $overrides);
        foreach ($params as $key => $value) {
            if ($value === '' || $value === null) {
                unset($params[$key]);
            }
        }
        return BASE_URL . '/shop.php?' . http_build_query($params);
    };
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
            <?php if ($model !== ''): ?><input type="hidden" name="model" value="<?= htmlspecialchars($model) ?>"><?php endif; ?>
            <?php if ($color !== ''): ?><input type="hidden" name="color" value="<?= htmlspecialchars($color) ?>"><?php endif; ?>
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

    <section class="shop-chips" aria-label="<?= htmlspecialchars($L['shopFiltersLabel']) ?>">
        <div class="container">
            <div class="chip-row">
                <span class="eyebrow"><?= $L['shopFilterModel'] ?></span>
                <a class="chip<?= $model === '' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($chipUrl(['model' => ''])) ?>"><?= $L['shopFilterAll'] ?></a>
                <a class="chip<?= $model === 'classic' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($chipUrl(['model' => 'classic'])) ?>"><?= $L['ipodClassic'] ?></a>
                <a class="chip<?= $model === 'mini' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($chipUrl(['model' => 'mini'])) ?>"><?= $L['ipodMini'] ?></a>
                <a class="chip<?= $model === 'shuffle' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($chipUrl(['model' => 'shuffle'])) ?>"><?= $L['ipodShuffle'] ?></a>
            </div>
            <div class="chip-row">
                <span class="eyebrow"><?= $L['shopFilterColor'] ?></span>
                <a class="chip<?= $color === '' ? ' is-active' : '' ?>" href="<?= htmlspecialchars($chipUrl(['color' => ''])) ?>"><?= $L['shopFilterAll'] ?></a>
                <?php
                    $colorChips = [
                        'blue' => ['label' => $L['colourBlue'], 'swatch' => '#16A3D9'],
                        'green' => ['label' => $L['colourGreen'], 'swatch' => '#3DB53A'],
                        'silver' => ['label' => $L['colourSilver'], 'swatch' => '#C9CACC'],
                        'red' => ['label' => $L['colourRed'], 'swatch' => '#D6262E'],
                        'purple' => ['label' => $L['colourPurple'], 'swatch' => '#86207F'],
                        'black' => ['label' => $L['colourBlack'], 'swatch' => '#2C2E33'],
                        'pink' => ['label' => $L['colourPink'], 'swatch' => '#E8A0BF'],
                        'gold' => ['label' => $L['colourGold'], 'swatch' => '#C9A227'],
                    ];
                    foreach ($colorChips as $key => $meta):
                ?>
                    <a class="chip chip--swatch<?= $color === $key ? ' is-active' : '' ?>"
                       href="<?= htmlspecialchars($chipUrl(['color' => $key])) ?>">
                        <i style="--c:<?= $meta['swatch'] ?>"></i><?= $meta['label'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="container section section--tight-top">
        <?php if (empty($products)): ?>
            <div class="empty empty--photo" style="--empty-img:url('<?= BASE_URL ?>/images/hero-fan.jpg')">
                <p><?= ($searchQuery !== '' || $model !== '' || $color !== '') ? $L['noSearchResults'] : $L['productsUnavailable'] ?></p>
                <?php if ($searchQuery !== '' || $model !== '' || $color !== ''): ?>
                    <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['clearSearch'] ?></a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php
                $showSoldBadge = false;
                require __DIR__ . '/templates/product-grid.php';
            ?>
        <?php endif; ?>
    </section>

    <?php require __DIR__ . '/templates/additional_info.php'; ?>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
