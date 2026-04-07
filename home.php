<?php
    // Requiring the needed files
    require __DIR__ . '/includes/lang.php';
    require __DIR__ . "/templates/header.php";
    $pageTitle = $L['homeTitle'];
    $pageDesc  = $L['homeDesc'];
?>
<main>
    <!-- Creating the hero section with big headline and buttons -->
    <section class="intro-home">
        <div>
            <h1><?php echo $L['heroHeadline']; ?></h1>
            <p><?php echo $L['heroSubheadline']; ?></p>
        </div>
        <div class="buttons">
            <a href="shop.php?lang=<?php echo $lang; ?>" class="button-home-primary"><?php echo $L['shopNow']; ?></a>
            <a href="about.php?lang=<?php echo $lang; ?>" class="button-home-secondary"><?php echo $L['learnMore']; ?></a>
        </div>
    </section>
    <!-- Section title above the iPod gallery -->
    <section class="info-home-header">
        <h2><?php echo $L['discoverClassics']; ?></h2>
    </section>
    <!-- Creating a main section for the home page -->
    <section class="info-home-page">
        <!-- Big iPod with headphones -->
        <div class="ipod-headphones">
            <img src="./images/ipod_headphones_home_page.png" alt="iPod with headphones">
        </div>
        <!-- Three small iPods which will be used as small image cards -->
        <div class="ipod-card">
            <img src="./images/ipod_classic_home_page.png" alt="iPod Classic">
            <h4><?php echo $L['ipodClassic']; ?></h4>
        </div>
        <div class="ipod-card">
            <img src="./images/ipod_mini_home_page.png" alt="iPod Mini">
            <h4><?php echo $L['ipodMini']; ?></h4>
        </div>
        <div class="ipod-card">
            <img src="./images/ipod_shuffle_home_page.png" alt="iPod Shuffle">
            <h4><?php echo $L['ipodShuffle']; ?></h4>
        </div>
        <!-- Brand story teaser -->
        <div class="brand-story">
            <h3><?php echo $L['brandStoryTitle']; ?></h3>
            <p><?php echo $L['brandStoryText']; ?></p>
            <div class="buttons">
                <a class="button-about-us" href="about.php?lang=<?php echo $lang; ?>"><?php echo $L['aboutUsButton']; ?></a>
            </div>
        </div>
    </section>
    <?php require "./templates/additional_info.php"; ?>
</main>
<?php require "./templates/footer.php"; ?>