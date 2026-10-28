<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/templates/header.php';

    $pageTitle = $L['resetPasswordTitle'];
    $pageDesc  = $L['resetPasswordDesc'];

    Session::start();
    if (Session::isLoggedIn()) {
        header("Location: " . (Session::get('is_admin') == 1 ? 'admin-dashboard.php' : 'personal-account.php'));
        exit();
    }

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
            $errors = ["Invalid or expired form submission. Please try again."];
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
?>
<main>
    <div class="login-reg-img-block">
        <a href="login.php" class="login-reg-img">
            <img src="images/login_ipod.png" alt="iPod photo">
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert_success"><p><?= $L['resetPasswordSuccess'] ?></p></div>
        <div class="buttons" style="justify-content:center; margin: 2rem 0;">
            <a href="login.php" class="button-home-primary"><?= $L['signInButton'] ?></a>
        </div>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="alert_failure">
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($reset): ?>
        <section class="form-section">
            <div class="form-instructions form-instructions-login-reg">
                <h3><?= $L['resetPasswordHeading'] ?></h3>
            </div>
            <div class="form-details">
                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <fieldset>
                        <label for="password"><?= $L['passwordLabelReg'] ?>:</label>
                        <input type="password" id="password" name="password"
                               placeholder="<?= $L['passwordPlaceholderReg'] ?>" required>
                        <ul id="password-rules" class="password-rules">
                            <li data-rule="length"><?= $L['ruleLength'] ?></li>
                            <li data-rule="upper"><?= $L['ruleUpper'] ?></li>
                            <li data-rule="lower"><?= $L['ruleLower'] ?></li>
                            <li data-rule="digit"><?= $L['ruleDigit'] ?></li>
                            <li data-rule="symbol"><?= $L['ruleSymbol'] ?></li>
                        </ul>
                        <label for="confirm_password"><?= $L['confirmPassword'] ?>:</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="<?= $L['confirmPasswordPlaceholder'] ?>" required>
                        <p id="password-match" class="password-match"
                           data-match-text="<?= htmlspecialchars($L['passwordsMatch']) ?>"
                           data-mismatch-text="<?= htmlspecialchars($L['passwordsMismatch']) ?>"></p>
                    </fieldset>
                    <div class="submit-button">
                        <button type="submit"><?= $L['resetPasswordButton'] ?></button>
                    </div>
                </form>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
