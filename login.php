<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/lang.php';

    Session::start();
    if (Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . (Session::get('is_admin') == 1 ? '/admin/' : '/personal-account.php'));
        exit();
    }

    $pageTitle = $L['loginTitle'];
    $pageDesc  = $L['loginDesc'];
    $errors = [];
    $info = [];
    if (isset($_GET['google_error'])) {
        $errors[] = $L['googleAuthFailed'];
    }
    if (isset($_GET['reauth'])) {
        $info[] = $L['reauthRequired'];
    }
    if (isset($_GET['confirmed'])) {
        $info[] = $L['confirmEmailSuccess'];
    }
    $googleEnabled = GoogleAuth::isConfigured();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $errors = ['Invalid or expired form submission. Please try again.'];
        } else {
            $database = new Database();
            $user = new User($database->connection);
            $name = trim($_POST['name'] ?? '');
            $password = $_POST['password'] ?? '';
            $logged_in_user = $user->login($name, $password);

            if ($logged_in_user) {
                Session::set('user_id',   $logged_in_user['id']);
                Session::set('username',  $logged_in_user['name']);
                Session::set('is_admin',  $logged_in_user['is_admin']);
                Cart::hydrateFromDbOnLogin((int) $logged_in_user['id']);
                header('Location: ' . BASE_URL . ($logged_in_user['is_admin'] == 1 ? '/admin/' : '/personal-account.php'));
                exit();
            }

            if (in_array('EMAIL_NOT_CONFIRMED', $user->error, true)) {
                $pending = $user->getPendingUnconfirmedUser();
                if ($pending) {
                    $token = $user->createEmailConfirmation((int) $pending['id']);
                    if ($token) {
                        $confirmUrl = APP_URL . '/confirm-email.php?token=' . urlencode($token);
                        Mailer::send(
                            $pending['email'],
                            $L['confirmEmailSubject'],
                            EmailTemplates::confirmEmail($L, $confirmUrl)
                        );
                    }
                }
                $errors = [$L['emailNotConfirmed']];
            } else {
                $errors = $user->error;
            }
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
                    <p class="eyebrow"><span class="dot"></span><?= $L['signInEyebrow'] ?></p>
                    <h1 class="display"><?= $L['welcomeBack'] ?></h1>
                    <p class="sub"><?= $L['signInSubtitle'] ?></p>
                </div>

                <?php if (!empty($info)): ?>
                    <div class="alert_success">
                        <?php foreach ($info as $msg): ?>
                            <p><?= htmlspecialchars($msg) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert_failure">
                        <?php foreach ($errors as $err): ?>
                            <p><?= htmlspecialchars($err) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="form-stack" action="<?= BASE_URL ?>/login.php?lang=<?= $lang ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <div class="field reveal reveal-2">
                        <label for="name"><?= $L['loginIdentifierLabel'] ?></label>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            <input class="input input--icon" type="text" id="name" name="name"
                                   placeholder="<?= $L['loginIdentifierPlaceholder'] ?>"
                                   autocomplete="username" required>
                        </div>
                    </div>
                    <div class="field reveal reveal-3">
                        <div class="field-row">
                            <label for="password"><?= $L['passwordLabel'] ?></label>
                            <a href="<?= BASE_URL ?>/forgot-password.php?lang=<?= $lang ?>"><?= $L['forgotPasswordLink'] ?></a>
                        </div>
                        <div class="input-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                            <input class="input input--icon input--toggle" type="password" id="password" name="password"
                                   placeholder="<?= $L['passwordPlaceholder'] ?>"
                                   autocomplete="current-password" required>
                            <button type="button" class="pw-toggle" aria-label="<?= $L['showPassword'] ?>" aria-controls="password" data-pw-toggle>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path class="eye-slash" d="M4 4l16 16"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--dark btn--lg btn--block btn--arrow reveal reveal-4">
                        <?= $L['signInButton'] ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                    </button>
                </form>

                <?php if ($googleEnabled): ?>
                <div class="auth2-divider reveal reveal-4"><span><?= $L['orContinueWith'] ?></span></div>
                <a href="<?= BASE_URL ?>/auth-google.php?lang=<?= $lang ?>" class="btn btn--google btn--lg btn--block reveal reveal-4">
                    <svg class="btn-google-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.03 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.03 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <?= $L['continueWithGoogle'] ?>
                </a>
                <?php endif; ?>

                <div class="auth2-divider reveal reveal-4"><span><?= $L['newToBrand'] ?></span></div>
                <a href="<?= BASE_URL ?>/register.php?lang=<?= $lang ?>" class="btn btn--outline btn--lg btn--block reveal reveal-4"><?= $L['createAccountCta'] ?></a>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
