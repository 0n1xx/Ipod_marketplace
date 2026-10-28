<?php
// Including the language file and starting the session so the nav
// can tell whether a visitor is logged in
require __DIR__ . '/../includes/lang.php';
// Session class is autoloaded via Composer (bootstrap.php, required by
// every page before this template); callers that skip bootstrap.php
// would need to require it explicitly
Session::start();

// Used to highlight the current page's link in the nav
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdminSection = str_contains($_SERVER['PHP_SELF'] ?? '', '/admin/');
$base = BASE_URL;
$cartHref = Session::isLoggedIn()
    ? $base . '/cart.php?lang=' . $lang
    : $base . '/login.php?lang=' . $lang;
$cartCount = Session::isLoggedIn() ? Cart::count() : 0;
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $pageDesc ?? ''; ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $pageTitle ?? 'QuébecIpod'; ?></title>
    <link rel="stylesheet" href="<?= $base ?>/css/style.css?v=20261028e">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <script src="<?= $base ?>/js/custom-js.js?v=20261028e" defer></script>
</head>
<body<?= $isAdminSection ? ' class="is-admin-page"' : '' ?>>
<header>
    <div>
        <a href="<?= $base ?>/index.php?lang=<?php echo $lang; ?>" class="logo">
            <img src="<?= $base ?>/images/logo.png" alt="QuébecIpod logo">
        </a>
        <p>QuébecIpod</p>
    </div>
    <nav>
        <menu>
            <li class="nav_menu"><a href="<?= $base ?>/index.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'index.php' && !$isAdminSection ? 'active' : '' ?>"><?php echo $L['navHome']; ?></a></li>
            <li class="nav_menu"><a href="<?= $base ?>/about.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'about.php' ? 'active' : '' ?>"><?php echo $L['navAbout']; ?></a></li>
            <li class="nav_menu"><a href="<?= $base ?>/shop.php?lang=<?php echo $lang; ?>" class="<?= in_array($currentPage, ['shop.php', 'product.php']) ? 'active' : '' ?>"><?php echo $L['navShop']; ?></a></li>
            <li class="nav_menu"><a href="<?= $base ?>/contact.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>"><?php echo $L['navContact']; ?></a></li>
            <?php if (Session::isLoggedIn()): ?>
                <?php $accountPage = Session::get('is_admin') == 1 ? $base . '/admin/' : $base . '/personal-account.php'; ?>
                <li class="nav_menu"><a href="<?= $accountPage ?>?lang=<?php echo $lang; ?>" class="<?= $isAdminSection || $currentPage === 'personal-account.php' ? 'active' : '' ?>"><?= htmlspecialchars(Session::get('username')) ?></a></li>
                <li class="nav_menu"><a href="<?= $base ?>/logout.php?lang=<?php echo $lang; ?>"><?php echo $L['logOut']; ?></a></li>
            <?php else: ?>
                <li class="nav_menu"><a href="<?= $base ?>/login.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'login.php' ? 'active' : '' ?>"><?php echo $L['navLogin']; ?></a></li>
                <li class="nav_menu"><a href="<?= $base ?>/register.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'register.php' ? 'active' : '' ?>"><?php echo $L['navRegister']; ?></a></li>
            <?php endif; ?>
        </menu>
    </nav>
    <div class="header-actions">
        <a href="<?= htmlspecialchars($cartHref) ?>"
           class="cart-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>"
           aria-label="<?= htmlspecialchars($L['navCart']) ?>">
            <svg class="cart-icon" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <?php if ($cartCount > 0): ?>
                <span class="cart-count"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
        <div class="lang-switch">
            <?php
            $newLang = ($lang === 'en') ? 'fr' : 'en';
            $params = $_GET;
            $params['lang'] = $newLang;
            $newUrl = '?' . http_build_query($params);

            if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr']) && $_GET['lang'] !== $lang) {
                setcookie('user_lang', $_GET['lang'], time() + (365 * 24 * 60 * 60), "/");
                header("Location: " . $_SERVER['PHP_SELF'] . ($params ? '?' . http_build_query($params) : ''));
                exit();
            }
            ?>
            <a href="<?= htmlspecialchars($newUrl) ?>" class="lang-btn">
                <?= strtoupper($newLang) ?>
            </a>
        </div>
        <div class="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</header>
