<?php
    // Require needed files for this page
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . "/templates/header.php";
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . "/includes/Session.php";
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . "/admin/User.php";

    $pageTitle = $L['registerTitle'];
    $pageDesc  = $L['registerDesc'];

    // Starting the session
    Session::start();
    // Checking if it is an admin, redirect to the dashboard, if not to a user profile page
    if (Session::isLoggedIn()) {
        if (Session::get("is_admin") == 1) {
            header("Location: admin-dashboard.php");
        } else {
            header("Location: personal-account.php");
        }
        exit();
    }

    /* Creating an empty variable which will store all of errors that we get from the user validation
     * Handing the form and trying to create a user
     */
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $database = new Database();
        $user = new User($database->connection);
        $name = trim($_POST["name"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $password = $_POST["password"] ?? '';
        $confirm_password = $_POST["confirm_password"] ?? '';
        $result = $user->register($name, $email, $password, $confirm_password);
        $errors = $user->error;
    }
?>
<main>
    <div class="login-reg-img-block">
        <a href="login.php" class="login-reg-img">
            <img src="images/login_ipod.png" alt="ipod phot">
        </a>
    </div>
    <!-- Displaying either a successful message that everything is good
    Or printing all of user mistakes that he made when he was filling up the form
    -->
    <?php if ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <?php if (!empty($errors)): ?>
            <div class="alert_failure">
                <p><?= $L["failRegister"] ?></p>
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert_success">
                <p><?= $L['successfulRegister']; ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <section class="form-section">
        <div class="form-instructions form-instructions-login-reg">
            <h3><?= $L['newToWebsite']; ?></h3>
            <p><?= $L['registerSubtitle']; ?></p>
        </div>
        <!-- The form that collects the data from our user -->
        <div class="form-details">
            <form action="" method="post">
                <fieldset>
                    <label for="name"><?= $L['fullName']; ?>:</label>
                    <input type="text" id="name" name="name"
                           placeholder="<?= $L['fullNamePlaceholder']; ?>" required>
                    <label for="email"><?= $L['emailLabelReg']; ?>:</label>
                    <input type="email" id="email" name="email"
                           placeholder="<?= $L['emailPlaceholderReg']; ?>" required>
                    <label for="password"><?= $L['passwordLabelReg']; ?>:</label>
                    <input type="password" id="password" name="password"
                           placeholder="<?= $L['passwordPlaceholderReg']; ?>" required>
                    <label for="confirm_password"><?= $L['confirmPassword']; ?>:</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="<?= $L['confirmPasswordPlaceholder']; ?>" required>
                </fieldset>
                <div class="submit-button">
                    <button type="submit"><?= $L['registerButton']; ?></button>
                </div>
            </form>
        </div>
        <!-- Adding a small a element that directs user if he already created an account -->
        <div class="register-log-in-div">
            <a href="login.php" class="register-log-in"><?= $L['alreadyHaveAccount']; ?></a>
        </div>
    </section>
</main>
<?php require "./templates/footer.php"; ?>