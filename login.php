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

            $errors = $user->error;
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

                <div class="auth2-divider reveal reveal-4"><span><?= $L['newToBrand'] ?></span></div>
                <a href="<?= BASE_URL ?>/register.php?lang=<?= $lang ?>" class="btn btn--outline btn--lg btn--block reveal reveal-4"><?= $L['createAccountCta'] ?></a>
            </div>
        </div>
    </div>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
