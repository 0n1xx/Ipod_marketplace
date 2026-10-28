<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    Session::start();
    if (Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . (Session::get('is_admin') == 1 ? '/admin/' : '/personal-account.php'));
        exit();
    }

    $pageTitle = $L['registerTitle'];
    $pageDesc  = $L['registerDesc'];
    $errors = [];
    $registered = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $errors = ['Invalid or expired form submission. Please try again.'];
        } else {
            $database = new Database();
            $user = new User($database->connection);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $result = $user->register($name, $email, $password, $confirm_password);
            $errors = $user->error;
            $registered = $result && empty($errors);
        }
    }

    require_once __DIR__ . '/templates/header.php';
?>
<main id="main">
    <section class="container auth">
        <div class="visual visual--sm">
            <img src="<?= BASE_URL ?>/images/auth-red.jpg" alt="Restored red iPod Classic" width="900" height="900">
            <p class="eyebrow visual-caption"><?= $L['registerSubtitle'] ?></p>
        </div>
        <div class="auth-card">
            <div>
                <h1 class="display"><?= $L['newToWebsite'] ?></h1>
                <p class="sub"><?= $L['registerSubtitle'] ?></p>
            </div>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <?php if ($registered): ?>
                    <div class="alert_success"><p><?= $L['successfulRegister'] ?></p></div>
                    <div class="success-box">
                        <p><?= $L['successfulRegister'] ?></p>
                        <p><a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>"><?= $L['signInButton'] ?></a></p>
                    </div>
                <?php else: ?>
                    <div class="alert_failure">
                        <p><?= $L['failRegister'] ?></p>
                        <?php foreach ($errors as $err): ?>
                            <p><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$registered): ?>
            <form class="form-stack" action="<?= BASE_URL ?>/register.php?lang=<?= $lang ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <div class="field">
                    <label for="name"><?= $L['userName'] ?></label>
                    <input class="input" type="text" id="name" name="name"
                           placeholder="<?= $L['userNamePlaceholder'] ?>" required>
                </div>
                <div class="field">
                    <label for="email"><?= $L['emailLabelReg'] ?></label>
                    <input class="input" type="email" id="email" name="email"
                           placeholder="<?= $L['emailPlaceholderReg'] ?>" required>
                </div>
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
                <button type="submit" class="btn btn--dark btn--lg btn--block"><?= $L['registerButton'] ?></button>
            </form>
            <?php endif; ?>
            <p class="switch"><?= $L['alreadyHaveAccount'] ?> <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>"><?= $L['navLogin'] ?></a></p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
