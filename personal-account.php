<?php
    // Requiring files needed for this page
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/templates/header.php';
    $pageTitle = $L['contactTitle'];
    $pageDesc  = $L['contactDesc'];

    // Starting a session
    Session::start();
    // If somehow user gets here, redirect to the login page
    if (!Session::isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    // If this is admin, redirects to the admin page
    if (Session::get('is_admin') == 1) {
        header("Location: admin-dashboard.php");
        exit();
    }
    $username = Session::get('username');

    $database = new Database();
    $orderModel = new Order($database->connection);
    $orders = $orderModel->getOrdersForUser((int) Session::get('user_id'));
?>
<main>
    <!-- The div with the personal greeting -->
    <div class="personal-greeting">
        <h2> <?php echo $L['personalGreeting'];?> <?php echo $username ?></h2>
    </div>
    <!-- Order history -->
    <section class="order-history">
        <h3 class="intro-form-admin"><?= $L['orderHistoryTitle'] ?></h3>
        <?php if (empty($orders)): ?>
            <p class="order-history-empty"><?= $L['orderHistoryEmpty'] ?></p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <span><?= htmlspecialchars($order['created_at']) ?></span>
                        <span>$<?= number_format((float)$order['total_price'], 2) ?></span>
                    </div>
                    <div class="order-card-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-card-item">
                                <img src="<?= htmlspecialchars($item['imagePath']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy">
                                <span><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <!-- Added some generic content that is diaplayed to the user -->
    <section class="thank-message">
        <p><?php echo $L['firstUserP'];?></p>
        <p><?php echo $L['secondUserP'];?></p>
        <p><?php echo $L['thirdUserP'];?></p>
        <p><?php echo $L['fourthUserP'];?></p>
        <p><?php echo $L['fifthUserP'];?></p>
        <p><?php echo $L['sixUserP'];?></p>
        <p><?php echo $L['seventhUserP'];?></p>
        <p><?php echo $L['eighthUserP'];?></p>
    </section>
    <!-- The log out button -->
    <div class="log-out-button-div">
        <a href="logout.php" class="log-out-button"><?php echo $L['logOut'];?></a>
    </div>
</main>
<!-- Adds the footer for this page -->
<?php require_once __DIR__ . '/templates/footer.php'; ?>