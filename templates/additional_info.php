<?php require __DIR__ . '/../includes/lang.php'; ?>
<section class="newsletter" aria-labelledby="nl-title">
    <div class="container">
        <div>
            <p class="eyebrow"><?= $L['newsletterEyebrow'] ?></p>
            <h2 id="nl-title" class="display"><?= $L['newsletterTitle'] ?></h2>
        </div>
        <div>
            <?php if (isset($_GET['subscribed'])): ?>
                <div class="alert_success"><p><?= $L['newsletterSuccess'] ?></p></div>
                <p class="newsletter-success" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>
                    <?= $L['newsletterSuccess'] ?>
                </p>
            <?php elseif (isset($_GET['subscribe_error'])): ?>
                <div class="alert_failure"><p><?= $L['newsletterError'] ?></p></div>
            <?php else: ?>
                <form class="newsletter-form" action="<?= BASE_URL ?>/subscribe.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
                    <label for="nl-email"><?= $L['newsletterPlaceholder'] ?></label>
                    <div class="pill-input">
                        <input id="nl-email" type="email" name="email" required placeholder="<?= $L['newsletterPlaceholder'] ?>" autocomplete="email">
                        <button type="submit" class="btn btn--dark"><?= $L['newsletterButton'] ?></button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
