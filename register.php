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
    <div class="container auth2">
        <figure class="auth2-media">
            <div class="kenburns">
                <img src="<?= BASE_URL ?>/images/register-unbox.jpg" alt="<?= $L['registerUnboxAlt'] ?>" width="1088" height="1456">
            </div>
            <figcaption class="auth2-caption">
                <p class="eyebrow"><?= $L['registerPerksEyebrow'] ?></p>
                <ul class="perks">
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
                        <?= $L['perk1'] ?>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8"/><path d="M8 12h8"/><path d="M8 16h5"/></svg>
                        <?= $L['perk2'] ?>
                    </li>
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                        <?= $L['perk3'] ?>
                    </li>
                </ul>
            </figcaption>
        </figure>

        <div class="auth2-panel">
            <div class="auth2-form">
                <div class="auth2-head reveal">
                    <p class="eyebrow"><span class="dot"></span><?= $L['createAccountEyebrow'] ?></p>
                    <h1 class="display"><?= $L['registerHeading'] ?></h1>
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
                    <div class="form-2col reveal reveal-2">
                        <div class="field">
                            <label for="name"><?= $L['userName'] ?></label>
                            <div class="input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                <input class="input input--icon" type="text" id="name" name="name"
                                       placeholder="<?= $L['userNamePlaceholder'] ?>"
                                       autocomplete="username" required>
                            </div>
                        </div>
                        <div class="field">
                            <label for="email"><?= $L['emailLabelReg'] ?></label>
                            <div class="input-wrap">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                                <input class="input input--icon" type="email" id="email" name="email"
                                       placeholder="<?= $L['emailPlaceholderReg'] ?>"
                                       autocomplete="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="field reveal reveal-3">
                        <label for="password"><?= $L['passwordLabelReg'] ?></label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                            <input class="input input--icon input--toggle" type="password" id="password" name="password"
                                   placeholder="<?= $L['passwordPlaceholderReg'] ?>"
                                   autocomplete="new-password" required
                                   aria-describedby="r-strength password-rules" data-password>
                            <button type="button" class="pw-toggle" aria-label="<?= $L['showPassword'] ?>" aria-controls="password" data-pw-toggle>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path class="eye-slash" d="M4 4l16 16"/></svg>
                            </button>
                        </div>
                        <div class="strength" id="r-strength" data-strength aria-live="polite">
                            <div class="strength-bar" aria-hidden="true"><span></span><span></span><span></span><span></span><span></span></div>
                            <span class="strength-label" data-strength-label><?= $L['passwordStrength'] ?></span>
                        </div>
                        <ul id="password-rules" class="rules">
                            <li data-rule="length"><?= $L['ruleLength'] ?></li>
                            <li data-rule="upper"><?= $L['ruleUpper'] ?></li>
                            <li data-rule="lower"><?= $L['ruleLower'] ?></li>
                            <li data-rule="digit"><?= $L['ruleDigit'] ?></li>
                            <li data-rule="symbol"><?= $L['ruleSymbol'] ?></li>
                        </ul>
                    </div>
                    <div class="field reveal reveal-4">
                        <label for="confirm_password"><?= $L['confirmPassword'] ?></label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                            <input class="input input--icon" type="password" id="confirm_password" name="confirm_password"
                                   placeholder="<?= $L['confirmPasswordPlaceholder'] ?>"
                                   autocomplete="new-password" required data-confirm>
                        </div>
                        <p class="match" aria-live="polite" data-match
                           data-ok="<?= htmlspecialchars($L['passwordsMatch']) ?>"
                           data-bad="<?= htmlspecialchars($L['passwordsMismatch']) ?>"
                           id="password-match"></p>
                    </div>
                    <button type="submit" class="btn btn--blue btn--lg btn--block btn--arrow reveal reveal-4">
                        <?= $L['registerButton'] ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </button>
                </form>
                <?php endif; ?>

                <p class="switch reveal reveal-4"><?= $L['alreadyHaveAccount'] ?> <a href="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>"><?= $L['navLogin'] ?></a></p>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
