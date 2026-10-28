<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/templates/header.php';

    $pageTitle = $L['forgotPasswordTitle'];
    $pageDesc  = $L['forgotPasswordDesc'];

    Session::start();
    if (Session::isLoggedIn()) {
        header("Location: " . (Session::get('is_admin') == 1 ? 'admin-dashboard.php' : 'personal-account.php'));
        exit();
    }

    $submitted = false;
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $errors = ["Invalid or expired form submission. Please try again."];
        } else {
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors = [$L['forgotPasswordInvalidEmail']];
            } else {
                $database = new Database();
                $user = new User($database->connection);
                $account = $user->findByEmail($email);

                // Always show the same success message whether or not the
                // email exists, so this page can't be used to find out who
                // has an account
                if ($account) {
                    $token = $user->createPasswordReset((int) $account['id']);
                    if ($token) {
                        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $resetUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . '/reset-password.php?token=' . $token;
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
?>
<main>
    <div class="login-reg-img-block">
        <a href="login.php" class="login-reg-img">
            <img src="images/login_ipod.png" alt="iPod photo">
        </a>
    </div>

    <?php if ($submitted): ?>
        <div class="alert_success"><p><?= $L['forgotPasswordSuccess'] ?></p></div>
    <?php elseif (!empty($errors)): ?>
        <div class="alert_failure">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="form-section">
        <div class="form-instructions form-instructions-login-reg">
            <h3><?= $L['forgotPasswordHeading'] ?></h3>
            <p><?= $L['forgotPasswordSubtitle'] ?></p>
        </div>
        <div class="form-details">
            <form action="" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <fieldset>
                    <label for="email"><?= $L['emailLabelReg'] ?>:</label>
                    <input type="email" id="email" name="email"
                           placeholder="<?= $L['emailPlaceholderReg'] ?>" required>
                </fieldset>
                <div class="submit-button">
                    <button type="submit"><?= $L['sendResetLinkButton'] ?></button>
                </div>
            </form>
        </div>
        <div class="register-log-in-div">
            <a href="login.php" class="register-log-in"><?= $L['alreadyHaveAccount'] ?></a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
