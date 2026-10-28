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
    <section class="container auth">
        <div class="visual visual--sm" aria-hidden="true">
            <img src="<?= BASE_URL ?>/images/auth-silver.jpg" alt="" width="900" height="900">
        </div>
        <div class="auth-card">
            <?php if ($success): ?>
                <div>
                    <h1><?= $L['resetPasswordHeading'] ?></h1>
                </div>
                <div class="alert_success"><p><?= $L['resetPasswordSuccess'] ?></p></div>
                <div class="success-box">
                    <p><?= $L['resetPasswordSuccess'] ?></p>
                </div>
                <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>" class="btn btn--dark btn--lg btn--block"><?= $L['signInButton'] ?></a>
            <?php else: ?>
                <div>
                    <h1><?= $L['resetPasswordHeading'] ?></h1>
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
                    <div class="field">
                        <label for="password"><?= $L['passwordLabelReg'] ?></label>
                        <input class="input" type="password" id="password" name="password"
                               placeholder="<?= $L['passwordPlaceholderReg'] ?>" required>
                        <ul id="password-rules" class="rules">
                            <li data-rule="length"><?= $L['ruleLength'] ?></li>
                            <li data-rule="upper"><?= $L['ruleUpper'] ?></li>
                            <li data-rule="lower"><?= $L['ruleLower'] ?></li>
                            <li data-rule="digit"><?= $L['ruleDigit'] ?></li>
                            <li data-rule="symbol"><?= $L['ruleSymbol'] ?></li>
                        </ul>
                    </div>
                    <div class="field">
                        <label for="confirm_password"><?= $L['confirmPassword'] ?></label>
                        <input class="input" type="password" id="confirm_password" name="confirm_password"
                               placeholder="<?= $L['confirmPasswordPlaceholder'] ?>" required>
                        <p id="password-match" class="match"
                           data-match-text="<?= htmlspecialchars($L['passwordsMatch']) ?>"
                           data-mismatch-text="<?= htmlspecialchars($L['passwordsMismatch']) ?>"></p>
                    </div>
                    <button type="submit" class="btn btn--dark btn--lg btn--block"><?= $L['resetPasswordButton'] ?></button>
                </form>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/forgot-password.php?lang=<?= $lang ?>" class="btn btn--outline btn--block"><?= $L['forgotPasswordLink'] ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
