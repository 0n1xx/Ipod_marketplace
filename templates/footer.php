<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-about">
                <p class="eyebrow">QuébecIpod</p>
                <p><?= $L['footerText'] ?></p>
            </div>
            <nav class="footer-col" aria-label="Footer">
                <p class="eyebrow"><?= $L['footerPages'] ?></p>
                <ul>
                    <li><a href="<?= BASE_URL ?>/index.php?lang=<?= $lang ?>"><?= $L['navHome'] ?></a></li>
                    <li><a href="<?= BASE_URL ?>/about.php?lang=<?= $lang ?>"><?= $L['navAbout'] ?></a></li>
                    <li><a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>"><?= $L['navShop'] ?></a></li>
                    <li><a href="<?= BASE_URL ?>/sold.php?lang=<?= $lang ?>"><?= $L['navSold'] ?></a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php?lang=<?= $lang ?>"><?= $L['navContact'] ?></a></li>
                </ul>
            </nav>
            <div class="footer-col">
                <p class="eyebrow"><?= $L['footerConnect'] ?></p>
                <ul>
                    <li><a href="https://www.facebook.com/yourpage" target="_blank" rel="noopener">Facebook ↗</a></li>
                    <li><a href="https://www.instagram.com/yourhandle" target="_blank" rel="noopener">Instagram ↗</a></li>
                    <li><a href="https://www.linkedin.com/in/yourprofile" target="_blank" rel="noopener">LinkedIn ↗</a></li>
                </ul>
            </div>
        </div>
        <p class="footer-wordmark" aria-hidden="true">QuébecIpod</p>
        <div class="footer-bottom">
            <span><?= $L['footerBottom'] ?></span>
            <span>English / Français</span>
        </div>
    </div>
</footer>
</body>
</html>
