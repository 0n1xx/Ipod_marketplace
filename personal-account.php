<?php
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    Session::start();
    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
    if (Session::get('is_admin') == 1) {
        header('Location: ' . BASE_URL . '/admin/');
        exit();
    }

    $pageTitle = $L['personalGreeting'];
    $pageDesc  = $L['personalGreeting'];
    $username = Session::get('username');

    $database = new Database();
    $orderModel = new Order($database->connection);
    $orders = $orderModel->getOrdersForUser((int) Session::get('user_id'));

    require_once __DIR__ . '/templates/header.php';
?>
<main id="main">
    <div class="container account-page">
        <section class="account-head">
            <div>
                <p class="eyebrow"><?= htmlspecialchars($username) ?></p>
                <h1><?= $L['personalGreeting'] ?></h1>
            </div>
            <a href="<?= BASE_URL ?>/logout.php?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['logOut'] ?></a>
        </section>

        <div class="account-layout">
            <section class="orders">
                <h2><?= $L['orderHistoryTitle'] ?></h2>
                <?php if (empty($orders)): ?>
                    <div class="empty empty--photo" style="--empty-img:url('<?= BASE_URL ?>/images/register-unbox.jpg')">
                        <p><?= $L['orderHistoryEmpty'] ?></p>
                        <a href="<?= BASE_URL ?>/shop.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['backToShopButton'] ?></a>
                    </div>
                <?php else: ?>
                    <ul class="order-list">
                        <?php foreach ($orders as $order): ?>
                            <?php $status = Order::normalizeStatus($order['status'] ?? 'paid'); ?>
                            <li class="order-card">
                                <div class="order-card-head">
                                    <span>#<?= (int)$order['id'] ?> · <?= htmlspecialchars($order['created_at']) ?></span>
                                    <strong>$<?= number_format((float)$order['total_price'], 2) ?></strong>
                                </div>
                                <?php require __DIR__ . '/templates/order-timeline.php'; ?>
                                <ul class="order-items">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li>
                                            <img src="<?= htmlspecialchars($item['imagePath']) ?>"
                                                 alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" width="72" height="72">
                                            <span><?= htmlspecialchars($item['name']) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <aside class="note">
                <p class="eyebrow"><?= $L['navHome'] ?></p>
                <p><?= $L['firstUserP'] ?></p>
                <p><?= $L['eighthUserP'] ?></p>
            </aside>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/templates/footer.php'; ?>
