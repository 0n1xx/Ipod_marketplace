<?php
    require_once __DIR__ . '/../includes/bootstrap.php';
    require_once __DIR__ . '/../includes/lang.php';
    require_once __DIR__ . '/_helpers.php';

    adminRequireAuth();

    $pageTitle = $L['adminOrdersTitle'];
    $pageDesc  = $L['adminOrdersIntro'];
    $success = null;

    $database = new Database();
    $orderModel = new Order($database->connection);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $orderModel->error[] = 'Invalid or expired form submission. Please try again.';
        } else {
            $orderId = (int)($_POST['order_id'] ?? 0);
            $status = Order::normalizeStatus($_POST['status'] ?? '');
            if ($orderId > 0 && $orderModel->updateStatus($orderId, $status)) {
                $success = $L['adminOrderStatusUpdated'];
            } elseif (empty($orderModel->error)) {
                $orderModel->error[] = $L['adminOrderStatusFailed'];
            }
        }
    }

    $orders = $orderModel->getAllOrders();
    $counts = ['paid' => 0, 'shipped' => 0, 'delivered' => 0];
    foreach ($orders as $order) {
        $counts[$order['status']] = ($counts[$order['status']] ?? 0) + 1;
    }

    require_once __DIR__ . '/../templates/header.php';
?>
<main id="main">
    <div class="container admin-page">
        <?php if ($success): ?>
            <div class="alert_success"><p><?= htmlspecialchars($success) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($orderModel->error)): ?>
            <div class="alert_failure">
                <?php foreach ($orderModel->error as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="admin-head">
            <div>
                <p class="eyebrow"><?= $L['adminWelcome'] ?></p>
                <h1 class="display display--md"><?= $L['adminOrdersTitle'] ?></h1>
                <p class="body"><?= $L['adminOrdersIntro'] ?></p>
            </div>
            <dl class="stats">
                <div class="stat">
                    <dt><?= $L['orderStatus_paid'] ?></dt>
                    <dd><?= (int)$counts['paid'] ?></dd>
                </div>
                <div class="stat stat--blue">
                    <dt><?= $L['orderStatus_shipped'] ?></dt>
                    <dd><?= (int)$counts['shipped'] ?></dd>
                </div>
                <div class="stat">
                    <dt><?= $L['orderStatus_delivered'] ?></dt>
                    <dd><?= (int)$counts['delivered'] ?></dd>
                </div>
            </dl>
        </section>

        <section class="panel">
            <div class="panel-head">
                <h2><?= $L['adminOrdersTitle'] ?></h2>
                <a href="<?= BASE_URL ?>/admin/?lang=<?= $lang ?>" class="btn btn--ghost btn--sm"><?= $L['adminInventoryTitle'] ?></a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty">
                    <p><?= $L['adminOrdersEmpty'] ?></p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table table--orders">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= $L['adminOrderCustomer'] ?></th>
                                <th><?= $L['adminOrderItems'] ?></th>
                                <th><?= $L['cartTotal'] ?></th>
                                <th><?= $L['orderStatusLabel'] ?></th>
                                <th><?= $L['adminOrderDate'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <?php $status = $order['status']; ?>
                                <tr>
                                    <td><strong>#<?= (int)$order['id'] ?></strong></td>
                                    <td>
                                        <div class="prod-cell" style="gap:8px">
                                            <span><?= htmlspecialchars($order['customer_name']) ?></span>
                                        </div>
                                        <span class="eyebrow" style="margin-top:4px"><?= htmlspecialchars($order['customer_email']) ?></span>
                                    </td>
                                    <td>
                                        <ul class="order-items order-items--compact">
                                            <?php foreach ($order['items'] as $item): ?>
                                                <li>
                                                    <img src="<?= htmlspecialchars($item['imagePath']) ?>" alt="" width="40" height="40" loading="lazy">
                                                    <span><?= htmlspecialchars($item['name']) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td>$<?= number_format((float)$order['total_price'], 2) ?></td>
                                    <td>
                                        <form method="post" class="status-form">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                            <span class="pill pill--status-<?= htmlspecialchars($status) ?>"><?= $L['orderStatus_' . $status] ?></span>
                                            <select name="status" class="select select--sm" aria-label="<?= $L['orderStatusLabel'] ?>">
                                                <?php foreach (Order::STATUSES as $option): ?>
                                                    <option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>>
                                                        <?= $L['orderStatus_' . $option] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn--ghost btn--sm"><?= $L['adminOrderUpdate'] ?></button>
                                        </form>
                                    </td>
                                    <td class="mono" style="font-size:12px"><?= htmlspecialchars($order['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
