<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/lang.php';

    $pageTitle = $L['homeTitle'];
    $pageDesc  = $L['homeDesc'];

    require __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container hero">
        <div class="hero-copy">
            <p class="eyebrow reveal"><span class="dot"></span><?= $L['homeEyebrow'] ?></p>
            <h1 class="display display--xl reveal reveal-2"><?= $L['heroHeadline'] ?></h1>
            <p class="lead reveal reveal-3"><?= $L['heroSubheadline'] ?></p>
            <div class="btn-row reveal reveal-3">
                <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark">
                    <?= $L['shopNow'] ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </a>
                <a href="<?= BASE_URL ?>/about.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['learnMore'] ?></a>
            </div>
        </div>
        <div class="hero-stage reveal reveal-2">
            <div class="visual">
                <img src="<?= BASE_URL ?>/images/hero-blue.jpg" alt="Restored iPod Classic in blue" width="900" height="900" data-hero-image>
            </div>
            <div class="swatches" role="group" aria-label="Choose a colour">
                <span class="eyebrow" data-hero-label>iPod Classic · Blue</span>
                <div class="swatch-row">
                    <button type="button" class="swatch" style="--c:#16A3D9" aria-pressed="true" aria-label="Blue" data-swatch="<?= BASE_URL ?>/images/hero-blue.jpg" data-name="Blue"></button>
                    <button type="button" class="swatch" style="--c:#3DB53A" aria-pressed="false" aria-label="Green" data-swatch="<?= BASE_URL ?>/images/green.jpg" data-name="Green"></button>
                    <button type="button" class="swatch" style="--c:#C9CACC" aria-pressed="false" aria-label="Silver" data-swatch="<?= BASE_URL ?>/images/auth-silver.jpg" data-name="Silver"></button>
                    <button type="button" class="swatch" style="--c:#D6262E" aria-pressed="false" aria-label="Red" data-swatch="<?= BASE_URL ?>/images/auth-red.jpg" data-name="Red"></button>
                    <button type="button" class="swatch" style="--c:#86207F" aria-pressed="false" aria-label="Purple" data-swatch="<?= BASE_URL ?>/images/purple.jpg" data-name="Purple"></button>
                </div>
            </div>
        </div>
    </section>

    <section class="ticker" aria-label="<?= htmlspecialchars($L['tickerLabel']) ?>">
        <div class="ticker-track">
            <ul>
                <li><?= $L['ticker1'] ?></li><li aria-hidden="true">✦</li>
                <li><?= $L['ticker2'] ?></li><li aria-hidden="true">✦</li>
                <li><?= $L['ticker3'] ?></li><li aria-hidden="true">✦</li>
                <li><?= $L['ticker4'] ?></li><li aria-hidden="true">✦</li>
                <li><?= $L['ticker5'] ?></li><li aria-hidden="true">✦</li>
            </ul>
            <ul aria-hidden="true">
                <li><?= $L['ticker1'] ?></li><li>✦</li>
                <li><?= $L['ticker2'] ?></li><li>✦</li>
                <li><?= $L['ticker3'] ?></li><li>✦</li>
                <li><?= $L['ticker4'] ?></li><li>✦</li>
                <li><?= $L['ticker5'] ?></li><li>✦</li>
            </ul>
        </div>
    </section>

    <section class="container section section--tight-bottom" aria-labelledby="classics">
        <div class="section-head">
            <h2 id="classics" class="display display--md"><?= $L['discoverClassics'] ?></h2>
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="link-arrow">
                <?= $L['seeFullShop'] ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
            </a>
        </div>
        <div class="cards-3">
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="model-card">
                <img src="<?= BASE_URL ?>/images/classic.jpg" alt="<?= $L['ipodClassic'] ?>" loading="lazy" width="900" height="900">
                <footer><h3><?= $L['ipodClassic'] ?></h3><span class="mono">01</span></footer>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="model-card">
                <img src="<?= BASE_URL ?>/images/mini.jpg" alt="<?= $L['ipodMini'] ?>" loading="lazy" width="900" height="900">
                <footer><h3><?= $L['ipodMini'] ?></h3><span class="mono">02</span></footer>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="model-card">
                <img src="<?= BASE_URL ?>/images/shuffle.jpg" alt="<?= $L['ipodShuffle'] ?>" loading="lazy" width="900" height="900">
                <footer><h3><?= $L['ipodShuffle'] ?></h3><span class="mono">03</span></footer>
            </a>
        </div>
    </section>

    <section class="container section section--tight-top">
        <div class="story">
            <img src="<?= BASE_URL ?>/images/story-desk.jpg" alt="" loading="lazy" width="900" height="900">
            <div class="story-copy">
                <p class="eyebrow"><?= $L['ourStory'] ?></p>
                <h2 class="display display--sm"><?= $L['brandStoryText'] ?></h2>
                <p class="body"><?= $L['homeStoryBody'] ?></p>
                <div><a href="<?= BASE_URL ?>/about.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['aboutUsButton'] ?></a></div>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/additional_info.php'; ?>
<?php require __DIR__ . '/templates/footer.php'; ?>
