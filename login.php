<?php
    // Requiring all needed files
    require __DIR__ . '/includes/lang.php';
    require __DIR__ . "/templates/header.php";
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . "/includes/Session.php";
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . "/admin/User.php";

    $pageTitle = $L['loginTitle'];
    $pageDesc  = $L['loginDesc'];
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
    $isSuccess = null;
    $error = [];
    // Getting the data from the table below
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $database = new Database();
        $user = new User($database->connection);
        $name = trim($_POST["name"] ?? '');
        $password = $_POST["password"] ?? '';
        $logged_in_user = $user->login($name, $password);
        /* If the login is successful, the user has to options, if it is an admin
         * then he will be able to access the admin-dashbord,
         * if it is a regular user, just his personal profile
         */
        if($logged_in_user){
            Session::set("user_id",   $logged_in_user["id"]);
            Session::set("username",  $logged_in_user["name"]);
            Session::set("is_admin",  $logged_in_user["is_admin"]);
            if ($logged_in_user["is_admin"] == 1) {
                header("Location: admin-dashboard.php");
            } else {
                header("Location: personal-account.php");
            }
            exit();
        }
        // If the login is not successful, taking the error which will be displayed
        else{
            $errors = $user->error;
        }
    }
?>
<main>
    <!-- Displaying each error that the user is facing -->
    <section class="messageSuccessFail">
    <?php if (!empty($errors)): ?>
        <div class="alert_failure">
            <?php foreach ($errors as $err): ?>
                <p><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </section>
    <!-- Displaying the logo of the login page -->
    <div class="login-reg-img-block">
        <a href="login.php" class="login-reg-img">
            <img src="images/login_ipod.png" alt="iPod photo">
        </a>
    </div>
    <section class="form-section">
        <div class="form-instructions form-instructions-login-reg">
            <h3><?php echo $L['welcomeBack']; ?></h3>
            <p><?php echo $L['signInSubtitle']; ?></p>
        </div>
        <!-- A section with the form that will be used to login for users -->
        <div class="form-details">
            <form action="login.php" method="post">
                <fieldset>
                    <label for="name"><?php echo $L['nameLabelReg']; ?>:</label>
                    <input type="text" id="name" name="name" placeholder="<?php echo $L['namePlaceholderReg']; ?>" required>
                    <label for="password"><?php echo $L['passwordLabel']; ?>:</label>
                    <input type="password" id="password" name="password" placeholder="<?php echo $L['passwordPlaceholder']; ?>" required>
                </fieldset>
                <div class="submit-button">
                    <button type="submit"><?php echo $L['signInButton']; ?></button>
                </div>
            </form>
        </div>
        <!-- Creating a button if a user is already registered -->
        <div>
            <a href="register.php" class="register-log-in"><?= $L['dontHaveAccount']; ?></a>
        </div>
    </section>
</main>
<?php require "./templates/footer.php"; ?>