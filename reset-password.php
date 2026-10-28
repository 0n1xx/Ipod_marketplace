<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    Session::start();
    if (Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . (Session::get('is_admin') == 1 ? '/admin/' : '/personal-account.php'));
        exit();
    }

    $pageTitle = $L['resetPasswordTitle'];
    $pageDesc  = $L['resetPasswordDesc'];

    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    $database = new Database();
    $user = new User($database->connection);

    $reset = $token !== '' ? $user->findValidReset($token) : null;
    $errors = [];
    $success = false;

    if (!$reset) {
        $errors = [$L['resetTokenInvalid']];
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $errors = ['Invalid or expired form submission. Please try again.'];
        } else {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $ok = $user->resetPassword((int) $reset['id'], (int) $reset['user_id'], $password, $confirmPassword);
            if ($ok) {
                $success = true;
            } else {
                $errors = $user->error;
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
            <?php if ($success): ?>
                <div class="auth2-head reveal">
                    <h1 class="display"><?= $L['resetPasswordHeading'] ?></h1>
                </div>
                <div class="alert_success"><p><?= $L['resetPasswordSuccess'] ?></p></div>
                <div class="success-box">
                    <p><?= $L['resetPasswordSuccess'] ?></p>
                </div>
                <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>" class="btn btn--dark btn--lg btn--block"><?= $L['signInButton'] ?></a>
            <?php else: ?>
                <div class="auth2-head reveal">
                    <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>" class="back-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg><?= $L['backToLogin'] ?></a>
                    <h1 class="display"><?= $L['resetPasswordHeading'] ?></h1>
                </div>
                <?php if (!empty($errors)): ?>
                    <div class="alert_failure">
                        <?php foreach ($errors as $err): ?>
                            <p><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($reset): ?>
                <form class="form-stack" action="<?= BASE_URL ?>/reset-password.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="field reveal reveal-2">
                        <label for="password"><?= $L['passwordLabelReg'] ?></label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                            <input class="input input--icon input--toggle" type="password" id="password" name="password"
                                   placeholder="<?= $L['passwordPlaceholderReg'] ?>" required data-password>
                            <button type="button" class="pw-toggle" aria-label="<?= $L['showPassword'] ?>" aria-controls="password" data-pw-toggle>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path class="eye-slash" d="M4 4l16 16"/></svg>
                            </button>
                        </div>
                        <ul id="password-rules" class="rules">
                            <li data-rule="length"><?= $L['ruleLength'] ?></li>
                            <li data-rule="upper"><?= $L['ruleUpper'] ?></li>
                            <li data-rule="lower"><?= $L['ruleLower'] ?></li>
                            <li data-rule="digit"><?= $L['ruleDigit'] ?></li>
                            <li data-rule="symbol"><?= $L['ruleSymbol'] ?></li>
                        </ul>
                    </div>
                    <div class="field reveal reveal-3">
                        <label for="confirm_password"><?= $L['confirmPassword'] ?></label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                            <input class="input input--icon" type="password" id="confirm_password" name="confirm_password"
                                   placeholder="<?= $L['confirmPasswordPlaceholder'] ?>" required data-confirm>
                        </div>
                        <p class="match" aria-live="polite" data-match
                           data-ok="<?= htmlspecialchars($L['passwordsMatch']) ?>"
                           data-bad="<?= htmlspecialchars($L['passwordsMismatch']) ?>"
                           id="password-match"></p>
                    </div>
                    <button type="submit" class="btn btn--dark btn--lg btn--block reveal reveal-4"><?= $L['resetPasswordButton'] ?></button>
                </form>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/forgot-password.php?lang=<?= $lang ?>" class="btn btn--outline btn--block"><?= $L['forgotPasswordLink'] ?></a>
                <?php endif; ?>
            <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
