<?php
    require_once __DIR__ . '/../includes/bootstrap.php';
    require_once __DIR__ . '/../includes/lang.php';
    require_once __DIR__ . '/_helpers.php';

    adminRequireAuth();

    $pageTitle = $L['adminWelcome'];
    $pageDesc  = $L['adminIntro'];
    $success = null;

    $database = new Database();
    $db = new Product($database->connection);

    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
    if ($isPost && !Csrf::verify($_POST['csrf_token'] ?? null)) {
        $db->error[] = 'Invalid or expired form submission. Please try again.';
        $isPost = false;
    }

    if ($isPost && isset($_POST['toggle_sold_id'])) {
        $toggleId = (int)$_POST['toggle_sold_id'];
        $product = $db->getProductById($toggleId);
        if ($product && $db->setSold($toggleId, !$product['is_sold'])) {
            $success = $product['is_sold'] ? 'Product marked as available!' : 'Product marked as sold!';
        }
    } elseif ($isPost && isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];
        $product = $db->getProductById($deleteId);
        if ($db->delete($deleteId)) {
            $localPath = $product['imagePath'] ?? '';
            if ($localPath !== '' && !str_starts_with($localPath, 'http') && file_exists(__DIR__ . '/../' . $localPath)) {
                unlink(__DIR__ . '/../' . $localPath);
            }
            $success = 'Product deleted successfully!';
        }
    }

    if (isset($_GET['updated'])) {
        $success = 'Product updated successfully!';
    }
    if (isset($_GET['created'])) {
        $success = 'Product created successfully!';
    }

    $products = $db->read();
    $soldCount = count(array_filter($products, fn($p) => $p['is_sold']));
    $availableCount = count($products) - $soldCount;

    require_once __DIR__ . '/../templates/header.php';
?>
<main id="main">
    <div class="container admin-page">
        <?php if ($success): ?>
            <div class="alert_success"><p><?= htmlspecialchars($success) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($db->error)): ?>
            <div class="alert_failure">
                <?php foreach ($db->error as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <section class="admin-head">
            <div>
                <p class="eyebrow">QuébecIpod</p>
                <h1 class="display display--md"><?= $L['adminWelcome'] ?></h1>
                <p class="body"><?= $L['adminIntro'] ?></p>
            </div>
            <dl class="stats">
                <div class="stat">
                    <dt><?= $L['adminStatTotal'] ?></dt>
                    <dd><?= count($products) ?></dd>
                </div>
                <div class="stat stat--blue">
                    <dt><?= $L['adminStatAvailable'] ?></dt>
                    <dd><?= $availableCount ?></dd>
                </div>
                <div class="stat">
                    <dt><?= $L['adminStatSold'] ?></dt>
                    <dd><?= $soldCount ?></dd>
                </div>
            </dl>
        </section>

        <section class="panel" id="inventory">
            <div class="panel-head">
                <h2><?= $L['adminInventoryTitle'] ?></h2>
                <div class="btn-row">
                    <a href="<?= BASE_URL ?>/admin/create.php?lang=<?= $lang ?>" class="btn btn--blue btn--sm"><?= $L['createNewProduct'] ?></a>
                    <a href="<?= BASE_URL ?>/admin/orders.php?lang=<?= $lang ?>" class="btn btn--ghost btn--sm"><?= $L['adminOrdersTitle'] ?></a>
                    <a href="<?= BASE_URL ?>/sold.php?lang=<?= $lang ?>" class="btn btn--ghost btn--sm"><?= $L['navSold'] ?></a>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty">
                    <p><?= $L['productsUnavailable'] ?></p>
                    <a href="<?= BASE_URL ?>/admin/create.php?lang=<?= $lang ?>" class="btn btn--dark"><?= $L['createNewProduct'] ?></a>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?= $L['adminNameLabel'] ?></th>
                                <th><?= $L['adminPriceLabel'] ?></th>
                                <th><?= $L['adminStatAvailable'] ?></th>
                                <th class="right"><?= $L['updateProduct'] ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="prod-cell">
                                            <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                                                 alt="" width="56" height="56" loading="lazy">
                                            <span><?= htmlspecialchars($product['name']) ?></span>
                                        </div>
                                    </td>
                                    <td>$<?= number_format((float)$product['price'], 2) ?></td>
                                    <td>
                                        <span class="pill<?= $product['is_sold'] ? ' pill--sold' : '' ?>">
                                            <?= $product['is_sold'] ? $L['soldBadge'] : $L['adminStatAvailable'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a href="<?= BASE_URL ?>/admin/edit.php?id=<?= (int)$product['product_id'] ?>&lang=<?= $lang ?>"
                                               class="btn btn--ghost btn--sm"><?= $L['updateProduct'] ?></a>
                                            <form method="post">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                                <input type="hidden" name="toggle_sold_id" value="<?= (int)$product['product_id'] ?>">
                                                <button type="submit" class="btn btn--ghost btn--sm">
                                                    <?= $product['is_sold'] ? $L['markAvailable'] : $L['markSold'] ?>
                                                </button>
                                            </form>
                                            <form method="post" class="js-confirm"
                                                  data-confirm="<?= htmlspecialchars($L['adminDeleteConfirm'], ENT_QUOTES) ?>"
                                                  data-confirm-title="<?= htmlspecialchars($L['adminDeleteTitle'], ENT_QUOTES) ?>"
                                                  data-confirm-ok="<?= htmlspecialchars($L['deleteProduct'], ENT_QUOTES) ?>"
                                                  data-confirm-cancel="<?= htmlspecialchars($L['adminCancelEdit'], ENT_QUOTES) ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                                <input type="hidden" name="delete_id" value="<?= (int)$product['product_id'] ?>">
                                                <button type="submit" class="btn btn--danger btn--sm"><?= $L['deleteProduct'] ?></button>
                                            </form>
                                        </div>
                                    </td>
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
