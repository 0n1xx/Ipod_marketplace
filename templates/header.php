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
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo $pageDesc ?? ''; ?>">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $pageTitle ?? 'QuébecIpod'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <!-- Adding Javascript -->
    <script src="js/custom-js.js" defer></script>
</head>
<body>
<header>
    <div>
        <a href="index.php?lang=<?php echo $lang; ?>" class="logo">
            <img src="images/logo.png" alt="QuébecIpod logo">
        </a>
        <p>QuébecIpod</p>
    </div>
    <!-- Adding the menu with all of the links to other pages -->
    <nav>
        <menu>
            <li class="nav_menu"><a href="index.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>"><?php echo $L['navHome']; ?></a></li>
            <li class="nav_menu"><a href="about.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'about.php' ? 'active' : '' ?>"><?php echo $L['navAbout']; ?></a></li>
            <li class="nav_menu"><a href="shop.php?lang=<?php echo $lang; ?>" class="<?= in_array($currentPage, ['shop.php', 'product.php']) ? 'active' : '' ?>"><?php echo $L['navShop']; ?></a></li>
            <li class="nav_menu"><a href="contact.php?lang=<?php echo $lang; ?>" class="<?= $currentPage === 'contact.php' ? 'active' : '' ?>"><?php echo $L['navContact']; ?></a></li>
            <?php if (Session::isLoggedIn()): ?>
                <?php $accountPage = Session::get('is_admin') == 1 ? 'admin-dashboard.php' : 'personal-account.php'; ?>
                <?php if (Session::get('is_admin') != 1): ?>
                    <li class="nav_menu">
                        <a href="cart.php?lang=<?php echo $lang; ?>">
                            <?= $L['navCart'] ?><?php if (Cart::count() > 0): ?> (<?= Cart::count() ?>)<?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="nav_menu"><a href="<?= $accountPage ?>?lang=<?php echo $lang; ?>"><?= htmlspecialchars(Session::get('username')) ?></a></li>
                <li class="nav_menu"><a href="logout.php?lang=<?php echo $lang; ?>"><?php echo $L['logOut']; ?></a></li>
            <?php else: ?>
                <li class="nav_menu"><a href="login.php?lang=<?php echo $lang; ?>"><?php echo $L['navLogin']; ?></a></li>
                <li class="nav_menu"><a href="register.php?lang=<?php echo $lang; ?>"><?php echo $L['navRegister']; ?></a></li>
            <?php endif; ?>
        </menu>
    </nav>
    <div>
        <div class="lang-switch">
            <?php
            // If the current language is english, there is an option to choose french and  vice versa
            $newLang = ($lang === 'en') ? 'fr' : 'en';

            $params = $_GET;
            $params['lang'] = $newLang;
            // Creating a new url which will depend on a language
            $newUrl = '?' . http_build_query($params);

            // Checking if the language is either english or french
            if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr']) && $_GET['lang'] !== $lang) {
                // Saving cookies for a year
                setcookie('user_lang', $_GET['lang'], time() + (365 * 24 * 60 * 60), "/");
                header("Location: " . $_SERVER['PHP_SELF'] . ($params ? '?' . http_build_query($params) : ''));
                exit();
            }
            ?>
            <a href="<?= htmlspecialchars($newUrl) ?>" class="lang-btn">
                <?= strtoupper($newLang) ?>
            </a>
        </div>
        <!-- Burger menu, it is only visible on tablets and phones -->
        <div class="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</header>