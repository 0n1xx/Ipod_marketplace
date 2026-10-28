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
                    <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>" class="back-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg><?= $L['backToLogin'] ?></a>
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
                    <div class="field reveal reveal-2">
                        <label for="email"><?= $L['emailLabelReg'] ?></label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                            <input class="input input--icon" type="email" id="email" name="email"
                                   placeholder="<?= $L['emailPlaceholderReg'] ?>" autocomplete="email" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--dark btn--lg btn--block reveal reveal-3"><?= $L['sendResetLinkButton'] ?></button>
                </form>
            <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
