<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/lang.php';

Session::start();

$pageTitle = $L['confirmEmailTitle'];
$pageDesc  = $L['confirmEmailDesc'];
$ok = false;
$errors = [];

$token = $_GET['token'] ?? '';
if (!is_string($token) || $token === '') {
    $errors[] = $L['confirmEmailInvalid'];
} else {
    $database = new Database();
    $user = new User($database->connection);
    if ($user->confirmEmailWithToken($token)) {
        $ok = true;
    } else {
        $errors = $user->error ?: [$L['confirmEmailInvalid']];
    }
}

require __DIR__ . '/templates/header.php';
?>
<main id="main">
    <div class="container auth2">
        <figure class="auth2-media">
            <div class="kenburns">
                <img src="<?= BASE_URL ?>/images/login-desk.jpg" alt="<?= $L['loginDeskAlt'] ?>" width="1088" height="1456">
            </div>
            <figcaption class="auth2-caption">
                <p class="eyebrow">QuébecIpod</p>
                <p class="auth2-quote"><?= $L['loginQuote'] ?></p>
            </figcaption>
        </figure>
        <div class="auth2-panel">
            <div class="auth2-form">
                <div class="auth2-head reveal">
                    <h1 class="display"><?= $L['confirmEmailHeading'] ?></h1>
                </div>
                <?php if ($ok): ?>
                    <div class="alert_success"><p><?= $L['confirmEmailSuccess'] ?></p></div>
                    <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>&confirmed=1" class="btn btn--dark btn--lg btn--block"><?= $L['signInButton'] ?></a>
                <?php else: ?>
                    <div class="alert_failure">
                        <?php foreach ($errors as $err): ?>
                            <p><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>" class="btn btn--outline btn--lg btn--block"><?= $L['backToLogin'] ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
