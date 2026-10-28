<footer>
    <section class="footer-logo">
        <div>
            <a href="<?= BASE_URL ?>/index.php?lang=<?php echo $lang; ?>" class="logo">
                <img src="<?= BASE_URL ?>/images/logo.png" alt="QuébecIpod logo">
            </a>
            <p>QuébecIpod</p>
        </div>
        <div>
            <p><?php echo $L['footerText']; ?></p>
        </div>
    </section>
    <section class="footer-links">
        <nav>
            <menu>
                <li class="nav_menu"><a href="<?= BASE_URL ?>/index.php?lang=<?php echo $lang; ?>"><?php echo $L['navHome']; ?></a></li>
                <li class="nav_menu"><a href="<?= BASE_URL ?>/about.php?lang=<?php echo $lang; ?>"><?php echo $L['navAbout']; ?></a></li>
                <li class="nav_menu"><a href="<?= BASE_URL ?>/shop.php?lang=<?php echo $lang; ?>"><?php echo $L['navShop']; ?></a></li>
                <li class="nav_menu"><a href="<?= BASE_URL ?>/contact.php?lang=<?php echo $lang; ?>"><?php echo $L['navContact']; ?></a></li>
            </menu>
        </nav>
    </section>
    <section class="footer-social-networks">
        <div>
            <p><?php echo $L['footerConnect']; ?></p>
            <a href="https://www.facebook.com/yourpage" target="_blank"><img src="<?= BASE_URL ?>/images/facebook_logo.png" alt="Facebook"></a>
            <a href="https://www.instagram.com/yourhandle" target="_blank"><img src="<?= BASE_URL ?>/images/instagram_logo.png" alt="Instagram"></a>
            <a href="https://www.linkedin.com/in/yourprofile" target="_blank"><img src="<?= BASE_URL ?>/images/linkedin_logo.png" alt="LinkedIn"></a>
        </div>
    </section>
</footer>
</body>
</html>
