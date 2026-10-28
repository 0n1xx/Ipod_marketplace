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
    <section class="container auth">
        <div class="visual">
            <img src="<?= BASE_URL ?>/images/login_ipod.png" alt="" width="900" height="900">
        </div>
        <div class="auth-card">
            <div>
                <h1><?= $L['welcomeBack'] ?></h1>
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
                <div class="field">
                    <label for="name"><?= $L['loginIdentifierLabel'] ?></label>
                    <input class="input" type="text" id="name" name="name"
                           placeholder="<?= $L['loginIdentifierPlaceholder'] ?>" required>
                </div>
                <div class="field">
                    <div class="field-row">
                        <label for="password"><?= $L['passwordLabel'] ?></label>
                        <a href="<?= BASE_URL ?>/forgot-password.php?lang=<?= $lang ?>"><?= $L['forgotPasswordLink'] ?></a>
                    </div>
                    <input class="input" type="password" id="password" name="password"
                           placeholder="<?= $L['passwordPlaceholder'] ?>" required>
                </div>
                <button type="submit" class="btn btn--dark btn--lg btn--block"><?= $L['signInButton'] ?></button>
            </form>
            <p class="switch"><?= $L['dontHaveAccount'] ?> <a href="<?= BASE_URL ?>/register.php?lang=<?= $lang ?>"><?= $L['navRegister'] ?></a></p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
