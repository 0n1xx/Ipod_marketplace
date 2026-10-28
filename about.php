<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/lang.php';
    $pageTitle = $lang === 'fr' ? 'À propos' : 'About';
    $pageDesc  = $lang === 'fr' ? 'Découvrez l\'histoire de QuébecIpod.' : 'Learn about QuébecIpod\'s story.';
    require __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container about-head">
        <p class="eyebrow"><?= $L['ourStory'] ?></p>
        <h1><?= $L['keepMusic'] ?></h1>
    </section>

    <section class="container">
        <ol class="numbered">
            <li><span class="num" aria-hidden="true">01</span><p><?= $L['aboutText1'] ?></p></li>
            <li><span class="num" aria-hidden="true">02</span><p><?= $L['aboutText2'] ?></p></li>
            <li><span class="num" aria-hidden="true">03</span><p><?= $L['aboutText3'] ?></p></li>
            <li><span class="num" aria-hidden="true">04</span><p><?= $L['aboutText4'] ?></p></li>
        </ol>
    </section>

    <section class="dark-band">
        <div class="container mission">
            <div class="mission-copy">
                <p class="eyebrow"><?= $L['ourMission'] ?></p>
                <h2 class="display display--sm"><?= $L['missionLine1'] ?></h2>
                <ul class="checklist">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                        <?= $L['missionLine2'] ?>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                        <?= $L['missionLine3'] ?>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                        <?= $L['missionLine4'] ?>
                    </li>
                </ul>
            </div>
            <figure class="flag">
                <img src="<?= BASE_URL ?>/images/quebecflag.png" alt="Drapeau du Québec" width="420" height="280" loading="lazy">
                <figcaption><?= $L['craftedQuebec'] ?></figcaption>
            </figure>
        </div>
    </section>

    <section class="container cta">
        <div>
            <h2><?= $L['readyRestore'] ?></h2>
            <p class="body"><?= $L['joinCustomers'] ?></p>
        </div>
        <div class="btn-row">
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['shopNow'] ?></a>
            <a href="<?= BASE_URL ?>/contact.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['getInTouch'] ?></a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
