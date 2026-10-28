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

    // Keep sold flags aligned with paid order_items (repairs accidental clears)
    require_once __DIR__ . '/../src/CatalogSeeder.php';
    CatalogSeeder::resyncSoldFromOrders($database->connection);

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
    } elseif ($isPost && !empty($_FILES['product_image']['name'])) {
        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => trim($_POST['price'] ?? ''),
            'imagePath'   => $_FILES['product_image'],
        ];

        $newProductId = $db->create($data);
        if ($newProductId) {
            $success = 'Product created successfully!';
            if (!empty($_FILES['extra_images']['name'][0])) {
                $db->addGalleryImages($newProductId, normalizeMultiFiles($_FILES['extra_images']));
            }
        }
    }

    if (isset($_GET['updated'])) {
        $success = 'Product updated successfully!';
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
                <a href="<?= BASE_URL ?>/sold.php?lang=<?= $lang ?>" class="btn btn--ghost btn--sm"><?= $L['navSold'] ?></a>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty">
                    <p><?= $L['productsUnavailable'] ?></p>
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
                                            <form method="post" class="js-confirm" data-confirm="<?= htmlspecialchars($L['adminDeleteConfirm'], ENT_QUOTES) ?>">
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

        <section class="panel" id="create">
            <div class="panel-head">
                <h2><?= $L['createNewProduct'] ?></h2>
            </div>
            <form class="form-stack" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <div class="form-grid">
                    <div class="field">
                        <label for="name"><?= $L['adminNameLabel'] ?></label>
                        <input class="input" type="text" id="name" name="name"
                               placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                    </div>
                    <div class="field">
                        <label for="price"><?= $L['adminPriceLabel'] ?></label>
                        <input class="input" type="text" id="price" name="price"
                               placeholder="<?= $L['adminPricePlaceholder'] ?>"
                               pattern="[0-9]+(\.\d{1,2})?$" required>
                    </div>
                    <div class="field field--full">
                        <label for="description"><?= $L['adminDescLabel'] ?></label>
                        <textarea class="input" id="description" name="description" rows="6"
                                  placeholder="<?= $L['adminDescPlaceholder'] ?>" required></textarea>
                    </div>
                    <div class="field">
                        <span class="label"><?= $L['adminImageLabel'] ?></span>
                        <label for="product_image_create" class="dropzone">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4"/><path d="M7 9l5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                            <strong><?= $L['adminImageLabel'] ?></strong>
                            <span><?= $L['adminImageHelp'] ?></span>
                        </label>
                        <input class="sr-only" type="file" id="product_image_create" name="product_image"
                               accept="image/jpeg,image/png,image/gif" required data-dropzone-input>
                    </div>
                    <div class="field">
                        <span class="label"><?= $L['adminGalleryLabel'] ?></span>
                        <label for="extra_images_create" class="dropzone">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="M7 3h12a2 2 0 0 1 2 2v12"/><path d="M3 15l4-4 4 4"/></svg>
                            <strong><?= $L['adminGalleryLabel'] ?></strong>
                            <span><?= $L['adminGalleryHelp'] ?></span>
                        </label>
                        <input class="sr-only" type="file" id="extra_images_create" name="extra_images[]"
                               accept="image/jpeg,image/png,image/gif" multiple data-dropzone-input>
                    </div>
                </div>
                <button type="submit" class="btn btn--blue btn--lg"><?= $L['adminCreateButton'] ?></button>
            </form>
        </section>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
