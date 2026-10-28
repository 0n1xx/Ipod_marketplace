<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    Session::start();
    if (Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . (Session::get('is_admin') == 1 ? '/admin/' : '/personal-account.php'));
        exit();
    }

    $pageTitle = $L['forgotPasswordTitle'];
    $pageDesc  = $L['forgotPasswordDesc'];
    $submitted = false;
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $errors = ['Invalid or expired form submission. Please try again.'];
        } else {
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors = [$L['forgotPasswordInvalidEmail']];
            } else {
                $database = new Database();
                $user = new User($database->connection);
                $account = $user->findByEmail($email);

                // Same success copy whether or not the email exists (no account enumeration)
                if ($account) {
                    $token = $user->createPasswordReset((int) $account['id']);
                    if ($token) {
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $resetUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/reset-password.php?token=' . $token;
                        Mailer::send(
                            $account['email'],
                            $L['resetEmailSubject'],
                            '<p>' . $L['resetEmailBody'] . '</p>'
                            . '<p><a href="' . htmlspecialchars($resetUrl) . '">' . htmlspecialchars($resetUrl) . '</a></p>'
                        );
                    }
                }
                $submitted = true;
            }
        }
    }

    require_once __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container auth">
        <div class="visual visual--sm">
            <img src="<?= BASE_URL ?>/images/auth-silver.jpg" alt="Restored silver iPod Classic" width="900" height="900">
            <p class="eyebrow visual-caption"><?= $L['forgotPasswordSubtitle'] ?></p>
        </div>
        <div class="auth-card">
            <div>
                <h1 class="display"><?= $L['forgotPasswordHeading'] ?></h1>
                <p class="sub"><?= $L['forgotPasswordSubtitle'] ?></p>
            </div>

            <?php if ($submitted): ?>
                <div class="alert_success"><p><?= $L['forgotPasswordSuccess'] ?></p></div>
                <div class="success-box">
                    <p><?= $L['forgotPasswordSuccess'] ?></p>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert_failure">
                        <?php foreach ($errors as $err): ?>
                            <p><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form class="form-stack" action="<?= BASE_URL ?>/forgot-password.php?lang=<?= $lang ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <div class="field">
                        <label for="email"><?= $L['emailLabelReg'] ?></label>
                        <input class="input" type="email" id="email" name="email"
                               placeholder="<?= $L['emailPlaceholderReg'] ?>" required>
                    </div>
                    <button type="submit" class="btn btn--dark btn--lg btn--block"><?= $L['sendResetLinkButton'] ?></button>
                </form>
            <?php endif; ?>
            <p class="switch"><a class="back-link" href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>"><?= $L['alreadyHaveAccount'] ?></a></p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
