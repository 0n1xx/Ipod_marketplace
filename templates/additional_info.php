<!-- Reusable element that I use on multiple pages -->
<?php require __DIR__ . '/../includes/lang.php'; ?>
<!-- Creating an additional info section that is also reused on the shop page -->
<section class="info-additional">
    <h3><?php echo $L['newsletterTitle']; ?></h3>
    <form action="#" method="post">
        <input type="email" class="additional-form-input" placeholder="<?php echo $L['newsletterPlaceholder']; ?>" required>
        <button type="submit" class="additional-form-button"><?php echo $L['newsletterButton']; ?></button>
    </form>
</section>