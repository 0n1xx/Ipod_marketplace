<footer>
    <section class="footer-logo">
        <div>
            <a href="home.php?lang=<?php echo $lang; ?>" class="logo">
                <img src="images/logo.png" alt="QuébecIpod logo">
            </a>
            <p>QuébecIpod</p>
        </div>
        <div>
            <p><?php echo $L['footerText']; ?></p>
        </div>
    </section>
    <!-- Creating a section with the links to the menu -->
    <section class="footer-links">
        <nav>
            <menu>
                <li class="nav_menu"><a href="home.php?lang=<?php echo $lang; ?>"><?php echo $L['navHome']; ?></a></li>
                <li class="nav_menu"><a href="about.php?lang=<?php echo $lang; ?>"><?php echo $L['navAbout']; ?></a></li>
                <li class="nav_menu"><a href="shop.php?lang=<?php echo $lang; ?>"><?php echo $L['navShop']; ?></a></li>
                <li class="nav_menu"><a href="contact.php?lang=<?php echo $lang; ?>"><?php echo $L['navContact']; ?></a></li>
            </menu>
        </nav>
    </section>
    <!-- Creating a section with social media links -->
    <section class="footer-social-networks">
        <div>
            <p><?php echo $L['footerConnect']; ?></p>
            <a href="https://www.facebook.com/yourpage" target="_blank"><img src="images/facebook_logo.png" alt="Facebook"></a>
            <a href="https://www.instagram.com/yourhandle" target="_blank"><img src="images/instagram_logo.png" alt="Instagram"></a>
            <a href="https://www.linkedin.com/in/yourprofile" target="_blank"><img src="images/linkedin_logo.png" alt="LinkedIn"></a>
        </div>
    </section>
</footer>
</body>
</html>