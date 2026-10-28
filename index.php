<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/lang.php';

    $pageTitle = $L['homeTitle'];
    $pageDesc  = $L['homeDesc'];

    require __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container hero2">
        <div class="hero2-copy">
            <p class="eyebrow reveal"><span class="dot"></span><?= $L['homeEyebrow'] ?></p>
            <h1 class="display hero2-title reveal reveal-2"><?= $L['heroHeadlineHtml'] ?></h1>
            <p class="lead reveal reveal-3"><?= $L['heroSubheadline'] ?></p>
            <div class="btn-row reveal reveal-3">
                <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark btn--lg btn--arrow">
                    <?= $L['shopNow'] ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </a>
                <a href="<?= BASE_URL ?>/about.php?lang=<?= $lang ?>" class="btn btn--outline btn--lg"><?= $L['learnMore'] ?></a>
            </div>
            <ul class="trust reveal reveal-4">
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.3-7 10-4-1.7-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/></svg>
                    <span><strong><?= $L['trust1Title'] ?></strong> <?= $L['trust1Body'] ?></span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="16" height="10" rx="2"/><path d="M21 11v2"/><path d="M11 9l-2 3h4l-2 3"/></svg>
                    <span><strong><?= $L['trust2Title'] ?></strong> <?= $L['trust2Body'] ?></span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h11v10H3z"/><path d="M14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/></svg>
                    <span><strong><?= $L['trust3Title'] ?></strong> <?= $L['trust3Body'] ?></span>
                </li>
            </ul>
        </div>
        <figure class="hero2-media reveal reveal-2">
            <div class="kenburns">
                <img src="<?= BASE_URL ?>/images/hero-fan.jpg" alt="<?= $L['heroFanAlt'] ?>" width="1136" height="1408" fetchpriority="high">
            </div>
            <figcaption class="glass-chip chip--top"><span class="pulse" aria-hidden="true"></span><?= $L['heroChip'] ?></figcaption>
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="glass-card">
                <span class="eyebrow"><?= $L['heroCardEyebrow'] ?></span>
                <span class="glass-card-title"><?= $L['heroCardTitle'] ?></span>
                <span class="glass-card-cta">
                    <?= $L['heroCardCta'] ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </span>
            </a>
        </figure>
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
        <div class="section-head" data-reveal>
            <div class="section-head-copy">
                <p class="eyebrow"><?= $L['collectionEyebrow'] ?></p>
                <h2 id="classics" class="display display--md"><?= $L['discoverClassics'] ?></h2>
            </div>
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="link-arrow">
                <?= $L['seeFullShop'] ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
            </a>
        </div>
        <div class="cards-3">
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="model-card" data-reveal style="--d: 0ms">
                <img src="<?= BASE_URL ?>/images/classic.jpg" alt="<?= $L['ipodClassic'] ?>" loading="lazy" width="900" height="900">
                <footer><h3><?= $L['ipodClassic'] ?></h3><span class="mono">01</span></footer>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="model-card" data-reveal style="--d: 90ms">
                <img src="<?= BASE_URL ?>/images/mini.jpg" alt="<?= $L['ipodMini'] ?>" loading="lazy" width="900" height="900">
                <footer><h3><?= $L['ipodMini'] ?></h3><span class="mono">02</span></footer>
            </a>
            <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="model-card" data-reveal style="--d: 180ms">
                <img src="<?= BASE_URL ?>/images/shuffle.jpg" alt="<?= $L['ipodShuffle'] ?>" loading="lazy" width="900" height="900">
                <footer><h3><?= $L['ipodShuffle'] ?></h3><span class="mono">03</span></footer>
            </a>
        </div>
    </section>

    <section class="container section section--tight-bottom" aria-labelledby="colours">
        <div class="section-head" data-reveal>
            <div class="section-head-copy">
                <p class="eyebrow"><?= $L['colourEyebrow'] ?></p>
                <h2 id="colours" class="display display--md"><?= $L['colourTitle'] ?></h2>
            </div>
        </div>
        <ul class="colour-row">
            <li data-reveal style="--d: 0ms"><a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="colour-tile"><img src="<?= BASE_URL ?>/images/blue.jpg" alt="" loading="lazy"><span><i style="--c:#16A3D9"></i><?= $L['colourBlue'] ?></span></a></li>
            <li data-reveal style="--d: 70ms"><a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="colour-tile"><img src="<?= BASE_URL ?>/images/green.jpg" alt="" loading="lazy"><span><i style="--c:#3DB53A"></i><?= $L['colourGreen'] ?></span></a></li>
            <li data-reveal style="--d: 140ms"><a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="colour-tile"><img src="<?= BASE_URL ?>/images/silver.jpg" alt="" loading="lazy"><span><i style="--c:#C9CACC"></i><?= $L['colourSilver'] ?></span></a></li>
            <li data-reveal style="--d: 210ms"><a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="colour-tile"><img src="<?= BASE_URL ?>/images/red.jpg" alt="" loading="lazy"><span><i style="--c:#D6262E"></i><?= $L['colourRed'] ?></span></a></li>
            <li data-reveal style="--d: 280ms"><a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="colour-tile"><img src="<?= BASE_URL ?>/images/purple.jpg" alt="" loading="lazy"><span><i style="--c:#86207F"></i><?= $L['colourPurple'] ?></span></a></li>
        </ul>
    </section>

    <section class="container section" aria-labelledby="process">
        <div class="process">
            <div class="process-head" data-reveal>
                <p class="eyebrow"><?= $L['processEyebrow'] ?></p>
                <h2 id="process" class="display display--md"><?= $L['processTitle'] ?></h2>
            </div>
            <figure class="process-photo process-photo--wide" data-reveal>
                <img src="<?= BASE_URL ?>/images/workshop.jpg" alt="<?= $L['workshopAlt'] ?>" loading="lazy" width="1680" height="944">
            </figure>
            <ol class="steps">
                <li data-reveal style="--d: 0ms">
                    <span class="step-num">01</span>
                    <h3><?= $L['ticker1'] ?></h3>
                    <p><?= $L['processStep1'] ?></p>
                </li>
                <li data-reveal style="--d: 90ms">
                    <span class="step-num">02</span>
                    <h3><?= $L['ticker2'] ?></h3>
                    <p><?= $L['processStep2'] ?></p>
                </li>
                <li data-reveal style="--d: 180ms">
                    <span class="step-num">03</span>
                    <h3><?= $L['ticker3'] ?></h3>
                    <p><?= $L['processStep3'] ?></p>
                </li>
            </ol>
            <figure class="process-photo process-photo--tall" data-reveal style="--d: 120ms">
                <img src="<?= BASE_URL ?>/images/battery.jpg" alt="<?= $L['batteryAlt'] ?>" loading="lazy" width="1136" height="1408">
            </figure>
        </div>
    </section>

    <section class="city-band" aria-labelledby="city">
        <div class="container city">
            <div class="city-copy" data-reveal>
                <p class="eyebrow"><?= $L['cityEyebrow'] ?></p>
                <h2 id="city" class="display display--lg"><?= $L['cityTitle'] ?></h2>
                <p class="body"><?= $L['cityBody'] ?></p>
                <div class="btn-row">
                    <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--blue btn--lg btn--arrow">
                        <?= $L['cityCta'] ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="<?= BASE_URL ?>/about.php?lang=<?= $lang ?>" class="btn btn--ghost-light btn--lg"><?= $L['ourStory'] ?></a>
                </div>
            </div>
            <figure class="city-photo" data-reveal style="--d: 120ms">
                <img src="<?= BASE_URL ?>/images/montreal.jpg" alt="<?= $L['montrealAlt'] ?>" loading="lazy" width="1136" height="1408">
            </figure>
        </div>
    </section>

    <section class="container section" aria-labelledby="faq-teaser">
        <div class="split" style="padding-block: 0">
            <div data-reveal>
                <p class="eyebrow"><?= $L['faqTeaserEyebrow'] ?></p>
                <h2 id="faq-teaser" class="display display--sm"><?= $L['faqTeaserTitle'] ?></h2>
                <div>
                    <a href="<?= BASE_URL ?>/contact.php?lang=<?= $lang ?>" class="link-arrow">
                        <?= $L['faqTeaserLink'] ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
            <div class="faq" data-reveal style="--d: 100ms">
                <details>
                    <summary><?= $L['faq3Question'] ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg></summary>
                    <p><?= $L['faq3Answer'] ?></p>
                </details>
                <details>
                    <summary><?= $L['faq2Question'] ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg></summary>
                    <p><?= $L['faq2Answer'] ?></p>
                </details>
                <details>
                    <summary><?= $L['faq4Question'] ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg></summary>
                    <p><?= $L['faq4Answer'] ?></p>
                </details>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/additional_info.php'; ?>
<?php require __DIR__ . '/templates/footer.php'; ?>
