<!-- Here it's only necessary to include the language php file -->
<?php require __DIR__ . '/../includes/lang.php'; ?>
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
        <a href="home.php?lang=<?php echo $lang; ?>" class="logo">
            <img src="images/logo.png" alt="QuébecIpod logo">
        </a>
        <p>QuébecIpod</p>
    </div>
    <!-- Adding the menu with all of the links to other pages -->
    <nav>
        <menu>
            <li class="nav_menu"><a href="home.php?lang=<?php echo $lang; ?>"><?php echo $L['navHome']; ?></a></li>
            <li class="nav_menu"><a href="about.php?lang=<?php echo $lang; ?>"><?php echo $L['navAbout']; ?></a></li>
            <li class="nav_menu"><a href="shop.php?lang=<?php echo $lang; ?>"><?php echo $L['navShop']; ?></a></li>
            <li class="nav_menu"><a href="contact.php?lang=<?php echo $lang; ?>"><?php echo $L['navContact']; ?></a></li>
            <li class="nav_menu"><a href="personal-account.php?lang=<?php echo $lang; ?>"><?php echo $L['personalAccount']; ?></a></li>
            <li class="nav_menu"><a href="login.php?lang=<?php echo $lang; ?>"><?php echo $L['navLogin']; ?></a></li>
            <li class="nav_menu"><a href="register.php?lang=<?php echo $lang; ?>"><?php echo $L['navRegister']; ?></a></li>
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