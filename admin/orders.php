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
    $columns = ['paid' => [], 'shipped' => [], 'delivered' => []];
    foreach ($orders as $order) {
        $columns[$order['status']][] = $order;
    }
    $nextStatus = ['paid' => 'shipped', 'shipped' => 'delivered', 'delivered' => null];

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
            <div class="btn-row">
                <a href="<?= BASE_URL ?>/admin/?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['adminInventoryTitle'] ?></a>
            </div>
        </section>

        <?php if (empty($orders)): ?>
            <div class="empty empty--photo" style="--empty-img:url('<?= BASE_URL ?>/images/workshop.jpg')">
                <p><?= $L['adminOrdersEmpty'] ?></p>
            </div>
        <?php else: ?>
            <section class="kanban" aria-label="<?= htmlspecialchars($L['adminOrdersTitle']) ?>">
                <?php foreach (Order::STATUSES as $columnStatus): ?>
                    <div class="kanban-col">
                        <header class="kanban-col__head">
                            <h2>
                                <span class="pill pill--status-<?= $columnStatus ?>"><?= $L['orderStatus_' . $columnStatus] ?></span>
                            </h2>
                            <span class="mono"><?= count($columns[$columnStatus]) ?></span>
                        </header>
                        <ul class="kanban-col__list">
                            <?php if (empty($columns[$columnStatus])): ?>
                                <li class="kanban-empty"><?= $L['adminKanbanEmpty'] ?></li>
                            <?php endif; ?>
                            <?php foreach ($columns[$columnStatus] as $order): ?>
                                <li class="kanban-card">
                                    <div class="kanban-card__top">
                                        <strong>#<?= (int)$order['id'] ?></strong>
                                        <span>$<?= number_format((float)$order['total_price'], 2) ?></span>
                                    </div>
                                    <p class="kanban-card__customer"><?= htmlspecialchars($order['customer_name']) ?></p>
                                    <p class="eyebrow"><?= htmlspecialchars($order['customer_email']) ?></p>
                                    <ul class="order-items order-items--compact">
                                        <?php foreach ($order['items'] as $item): ?>
                                            <li>
                                                <img src="<?= htmlspecialchars($item['imagePath']) ?>" alt="" width="40" height="40" loading="lazy">
                                                <span><?= htmlspecialchars($item['name']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <p class="mono kanban-card__date"><?= htmlspecialchars($order['created_at']) ?></p>
                                    <?php $advance = $nextStatus[$columnStatus]; ?>
                                    <?php if ($advance): ?>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                            <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                                            <input type="hidden" name="status" value="<?= $advance ?>">
                                            <button type="submit" class="btn btn--blue btn--sm btn--block">
                                                <?= $L['adminOrderAdvance'] ?> → <?= $L['orderStatus_' . $advance] ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <p class="eyebrow"><?= $L['adminOrderDone'] ?></p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
