<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require __DIR__ . '/includes/lang.php';

    Session::start();
    if (Session::isLoggedIn()) {
        if (Session::get('is_admin') == 1) {
            header('Location: ' . BASE_URL . '/admin/');
        } else {
            header('Location: ' . BASE_URL . '/personal-account.php');
        }
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
                if ($logged_in_user['is_admin'] == 1) {
                    header('Location: ' . BASE_URL . '/admin/');
                } else {
                    header('Location: ' . BASE_URL . '/personal-account.php');
                }
                exit();
            }

            $errors = $user->error;
        }
    }

    require __DIR__ . '/templates/header.php';
?>
<main>
    <section class="messageSuccessFail">
    <?php if (!empty($errors)): ?>
        <div class="alert_failure">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </section>
    <div class="login-reg-img-block">
        <a href="<?= BASE_URL ?>/login.php" class="login-reg-img">
            <img src="<?= BASE_URL ?>/images/login_ipod.png" alt="iPod photo">
        </a>
    </div>
    <section class="form-section">
        <div class="form-instructions form-instructions-login-reg">
            <h3><?php echo $L['welcomeBack']; ?></h3>
            <p><?php echo $L['signInSubtitle']; ?></p>
        </div>
        <div class="form-details">
            <form action="<?= BASE_URL ?>/login.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <fieldset>
                    <label for="name"><?php echo $L['loginIdentifierLabel']; ?>:</label>
                    <input type="text" id="name" name="name" placeholder="<?php echo $L['loginIdentifierPlaceholder']; ?>" required>
                    <label for="password"><?php echo $L['passwordLabel']; ?>:</label>
                    <input type="password" id="password" name="password" placeholder="<?php echo $L['passwordPlaceholder']; ?>" required>
                </fieldset>
                <a href="<?= BASE_URL ?>/forgot-password.php" class="forgot-password-link"><?= $L['forgotPasswordLink'] ?></a>
                <div class="submit-button">
                    <button type="submit"><?php echo $L['signInButton']; ?></button>
                </div>
            </form>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/register.php" class="register-log-in"><?= $L['dontHaveAccount']; ?></a>
        </div>
    </section>
</main>
<?php require __DIR__ . '/templates/footer.php'; ?>
