<!-- Reusable element that I use on multiple pages -->
<?php require __DIR__ . '/../includes/lang.php'; ?>
<!-- Creating an additional info section that is also reused on the shop page -->
<section class="info-additional">
    <h3><?php echo $L['newsletterTitle']; ?></h3>
    <?php if (isset($_GET['subscribed'])): ?>
        <div class="alert_success"><p><?= $L['newsletterSuccess'] ?></p></div>
    <?php elseif (isset($_GET['subscribe_error'])): ?>
        <div class="alert_failure"><p><?= $L['newsletterError'] ?></p></div>
    <?php endif; ?>
    <form action="subscribe.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
        <input type="hidden" name="redirect" value="<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>">
        <input type="email" name="email" class="additional-form-input" placeholder="<?php echo $L['newsletterPlaceholder']; ?>" required>
        <button type="submit" class="additional-form-button"><?php echo $L['newsletterButton']; ?></button>
    </form>
</section>