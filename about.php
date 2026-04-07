<?php
    // Added language file which will translate every en english or in french
    require __DIR__ . '/includes/lang.php';
    $pageTitle = $lang === 'fr' ? 'À propos' : 'About';
    $pageDesc  = $lang === 'fr' ? 'Découvrez l\'histoire de QuébecIpod.' : 'Learn about QuébecIpod\'s story.';
    require './templates/header.php';
?>
<!-- Instead of typing all the info, the content is displayed based on the language -->
<main>
    <section class="about-intro-section">
        <div>
            <h1 class="main-header"><?php echo $L['ourStory']; ?></h1>
            <p><?php echo $L['keepMusic']; ?></p>
        </div>
    </section>
    <!-- Added some general text about my company -->
    <section class="about-primary-section">
        <div class="about-company-fact"><p><?php echo $L['aboutText1']; ?></p></div>
        <div class="about-company-fact"><p><?php echo $L['aboutText2']; ?></p></div>
        <div class="about-company-fact"><p><?php echo $L['aboutText3']; ?></p></div>
        <div class="about-company-fact"><p><?php echo $L['aboutText4']; ?></p></div>
    </section>
    <section class="additional-company-fact">
        <div class="additional-company-fact-text">
            <h2><?php echo $L['ourMission']; ?></h2>
            <p><?php echo $L['missionLine1']; ?></p>
            <ul>
                <li><?php echo $L['missionLine2']; ?></li>
                <li><?php echo $L['missionLine3']; ?></li>
                <li><?php echo $L['missionLine4']; ?></li>
            </ul>
        </div>
        <div class="additional-company-fact-image-con">
            <a href="home.php?lang=<?php echo $lang; ?>" class="logo">
                <img src="images/quebecflag.png" alt="Drapeau du Québec">
            </a>
            <p><?php echo $L['craftedQuebec']; ?></p>
        </div>
    </section>
    <section class="restore">
        <div class="restore-info">
            <h2><?php echo $L['readyRestore']; ?></h2>
            <p><?php echo $L['joinCustomers']; ?></p>
        </div>
        <!-- Adds some interactive a elements -->
        <div class="buttons">
            <a href="team.php?lang=<?php echo $lang; ?>" class="button-about-primary"><?php echo $L['meetTeam']; ?></a>
            <a href="contact.php?lang=<?php echo $lang; ?>" class="button-about-secondary"><?php echo $L['getInTouch']; ?></a>
        </div>
    </section>
</main>
<?php require './templates/footer.php'; ?>