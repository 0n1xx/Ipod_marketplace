<?php
require __DIR__ . '/../includes/lang.php';
Session::start();

$currentPage = basename($_SERVER['PHP_SELF']);
$isAdminSection = str_contains($_SERVER['PHP_SELF'] ?? '', '/admin/');
$base = BASE_URL;
$cartHref = Session::isLoggedIn()
    ? $base . '/cart.php?lang=' . $lang
    : $base . '/login.php?lang=' . $lang;
$cartCount = Session::isLoggedIn() ? Cart::count() : 0;

$newLang = ($lang === 'en') ? 'fr' : 'en';
$params = $_GET;
$params['lang'] = $newLang;
$newUrl = '?' . http_build_query($params);

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr'], true) && $_GET['lang'] !== $lang) {
    setcookie('user_lang', $_GET['lang'], time() + (365 * 24 * 60 * 60), '/');
    header('Location: ' . $_SERVER['PHP_SELF'] . ($params ? '?' . http_build_query($params) : ''));
    exit();
}

$loggedIn = Session::isLoggedIn();
$isAdmin = $loggedIn && Session::get('is_admin') == 1;
$accountPage = null;
$username = '';
$initial = '';
if ($loggedIn) {
    $accountPage = $isAdmin ? $base . '/admin/' : $base . '/personal-account.php';
    $username = (string) Session::get('username');
    $initial = strtoupper(substr($username, 0, 1));
}

// Mobile menu numbers stay sequential even when Sold is hidden for non-admins
$mobileLinks = [
    ['href' => $base . '/index.php?lang=' . $lang, 'label' => $L['navHome'], 'current' => $currentPage === 'index.php' && !$isAdminSection],
    ['href' => $base . '/about.php?lang=' . $lang, 'label' => $L['navAbout'], 'current' => $currentPage === 'about.php'],
    ['href' => $base . '/shop.php?lang=' . $lang, 'label' => $L['navShop'], 'current' => in_array($currentPage, ['shop.php', 'product.php'], true)],
];
if ($isAdmin) {
    $mobileLinks[] = ['href' => $base . '/sold.php?lang=' . $lang, 'label' => $L['navSold'], 'current' => $currentPage === 'sold.php'];
}
$mobileLinks[] = ['href' => $base . '/contact.php?lang=' . $lang, 'label' => $L['navContact'], 'current' => $currentPage === 'contact.php'];
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($pageDesc ?? ''); ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($pageTitle ?? 'QuébecIpod'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=JetBrains+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="<?= $base ?>/css/style.css?v=20261028u">
    <script src="<?= $base ?>/js/custom-js.js?v=20261028u" defer></script>
</head>
<body<?= $isAdminSection ? ' class="is-admin-page"' : '' ?>>
<a href="#main" class="sr-only">Skip to content</a>
<header class="site-header">
    <div class="container header-bar">
        <a href="<?= $base ?>/index.php?lang=<?= $lang ?>" class="brand" aria-label="QuébecIpod home">
            <span class="brand-mark" aria-hidden="true"></span>
            <span class="brand-text">
                <span class="brand-name">QuébecIpod</span>
                <span class="brand-tag"><?= $L['brandTag'] ?></span>
            </span>
        </a>

        <nav class="nav" aria-label="Main">
            <ul>
                <li><a href="<?= $base ?>/index.php?lang=<?= $lang ?>" <?= $currentPage === 'index.php' && !$isAdminSection ? 'aria-current="page"' : '' ?>><?= $L['navHome'] ?></a></li>
                <li><a href="<?= $base ?>/about.php?lang=<?= $lang ?>" <?= $currentPage === 'about.php' ? 'aria-current="page"' : '' ?>><?= $L['navAbout'] ?></a></li>
                <li><a href="<?= $base ?>/shop.php?lang=<?= $lang ?>" <?= in_array($currentPage, ['shop.php', 'product.php'], true) ? 'aria-current="page"' : '' ?>><?= $L['navShop'] ?></a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="<?= $base ?>/sold.php?lang=<?= $lang ?>" <?= $currentPage === 'sold.php' ? 'aria-current="page"' : '' ?>><?= $L['navSold'] ?></a></li>
                <?php endif; ?>
                <li><a href="<?= $base ?>/contact.php?lang=<?= $lang ?>" <?= $currentPage === 'contact.php' ? 'aria-current="page"' : '' ?>><?= $L['navContact'] ?></a></li>
            </ul>
        </nav>

        <div class="header-actions">
            <a href="<?= htmlspecialchars($newUrl) ?>" class="lang-btn" lang="<?= $newLang ?>" aria-label="<?= $newLang === 'fr' ? 'Passer au français' : 'Switch to English' ?>"><?= strtoupper($newLang) ?></a>
            <a href="<?= htmlspecialchars($cartHref) ?>" class="header-link" aria-label="<?= htmlspecialchars($L['navCart']) ?>" <?= $currentPage === 'cart.php' ? 'aria-current="page"' : '' ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11.2a1.5 1.5 0 0 0 1.5 1.2h8.4a1.5 1.5 0 0 0 1.5-1.2L20 8H7"/></svg>
                <span><?= $L['navCart'] ?></span>
                <?php if ($cartCount > 0): ?>
                    <span class="count-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <?php if ($loggedIn): ?>
                <a href="<?= $accountPage ?>?lang=<?= $lang ?>" class="account-chip" <?= $isAdminSection || $currentPage === 'personal-account.php' ? 'aria-current="page"' : '' ?>>
                    <span class="avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
                    <?= htmlspecialchars($username) ?>
                </a>
                <a href="<?= $base ?>/logout.php?lang=<?= $lang ?>" class="header-link header-link--muted"><?= $L['logOut'] ?></a>
            <?php else: ?>
                <a href="<?= $base ?>/login.php?lang=<?= $lang ?>" class="header-link" <?= $currentPage === 'login.php' ? 'aria-current="page"' : '' ?>><?= $L['navLogin'] ?></a>
                <a href="<?= $base ?>/register.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['navRegister'] ?></a>
            <?php endif; ?>
        </div>

        <div class="header-mobile">
            <a href="<?= htmlspecialchars($cartHref) ?>" class="icon-btn" aria-label="<?= htmlspecialchars($L['navCart']) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11.2a1.5 1.5 0 0 0 1.5 1.2h8.4a1.5 1.5 0 0 0 1.5-1.2L20 8H7"/></svg>
                <?php if ($cartCount > 0): ?>
                    <span class="count-badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <button type="button" class="icon-btn burger" aria-expanded="false" aria-controls="mobile-menu" aria-label="Menu" data-menu-toggle>
                <span></span><span></span>
            </button>
        </div>
    </div>

    <div class="mobile-menu" id="mobile-menu" hidden>
        <div class="container">
            <nav aria-label="Mobile">
                <ul>
                    <?php foreach ($mobileLinks as $i => $link): ?>
                        <li>
                            <a href="<?= $link['href'] ?>" <?= $link['current'] ? 'aria-current="page"' : '' ?>>
                                <?= $link['label'] ?>
                                <span class="mono"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="mobile-menu-foot">
                <?php if ($loggedIn): ?>
                    <a href="<?= $accountPage ?>?lang=<?= $lang ?>" class="account-chip">
                        <span class="avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
                        <?= htmlspecialchars($username) ?>
                    </a>
                    <a href="<?= $base ?>/logout.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['logOut'] ?></a>
                <?php else: ?>
                    <a href="<?= $base ?>/login.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['navLogin'] ?></a>
                    <a href="<?= $base ?>/register.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['navRegister'] ?></a>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($newUrl) ?>" class="lang-btn" lang="<?= $newLang ?>" aria-label="<?= $newLang === 'fr' ? 'Passer au français' : 'Switch to English' ?>"><?= strtoupper($newLang) ?></a>
            </div>
        </div>
    </div>
</header>
