<?php
    require_once __DIR__ . '/../includes/bootstrap.php';
    require_once __DIR__ . '/../includes/lang.php';

    Session::start();
    if (!Session::isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
    if (Session::get('is_admin') != 1) {
        header('Location: ' . BASE_URL . '/personal-account.php');
        exit();
    }

    $pageTitle = $L['adminWelcome'];
    $pageDesc  = $L['adminIntro'];
    $success = null;

    $database = new Database();
    $db = new Product($database->connection);

    // PHP groups multi-file inputs as $_FILES['x']['name'][0..n]; reshape to per-file arrays
    function normalizeMultiFiles(array $filesField): array {
        $normalized = [];
        foreach ($filesField['name'] as $i => $name) {
            $normalized[] = [
                'name'     => $name,
                'tmp_name' => $filesField['tmp_name'][$i],
                'error'    => $filesField['error'][$i],
                'size'     => $filesField['size'][$i],
            ];
        }
        return $normalized;
    }

    // edit_id only selects a row for the form — no CSRF needed.
    // Mutating actions always require a valid token.
    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id']);
    if ($isPost && !Csrf::verify($_POST['csrf_token'] ?? null)) {
        $db->error[] = 'Invalid or expired form submission. Please try again.';
        $isPost = false;
    }

    if ($isPost && isset($_POST['update_id'])) {
        $updateId = (int)$_POST['update_id'];
        $product = $db->getProductById($updateId);

        $data = [
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'price'       => trim($_POST['price']),
            'oldImage'    => $product['imagePath'],
            'newImage'    => $_FILES['product_image'],
        ];

        if ($db->update($updateId, $data)) {
            $success = 'Product updated successfully!';
            if (!empty($_FILES['extra_images']['name'][0])) {
                $db->addGalleryImages($updateId, normalizeMultiFiles($_FILES['extra_images']));
            }
        }
    } elseif ($isPost && isset($_POST['delete_image_id'])) {
        $db->deleteGalleryImage((int)$_POST['delete_image_id'], (int)$_POST['delete_image_product_id']);
    } elseif ($isPost && isset($_POST['toggle_sold_id'])) {
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
            'name'        => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'price'       => trim($_POST['price']),
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

    $products = $db->read();
    $soldCount = count(array_filter($products, fn($p) => $p['is_sold']));
    $availableCount = count($products) - $soldCount;

    $editProduct = null;
    if (isset($_POST['edit_id'])) {
        $editProduct = $db->getProductById((int)$_POST['edit_id']);
    }

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
                                            <form method="post">
                                                <input type="hidden" name="edit_id" value="<?= (int)$product['product_id'] ?>">
                                                <button type="submit" class="btn btn--ghost btn--sm"><?= $L['updateProduct'] ?></button>
                                            </form>
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

        <?php if ($editProduct): ?>
            <section class="panel">
                <div class="panel-head">
                    <h2><?= $L['updateProduct'] ?> — <?= htmlspecialchars($editProduct['name']) ?></h2>
                    <a href="<?= BASE_URL ?>/admin/" class="btn btn--ghost btn--sm"><?= $L['adminCancelEdit'] ?></a>
                </div>
                <form class="form-stack" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <input type="hidden" name="update_id" value="<?= (int)$editProduct['product_id'] ?>">
                    <input type="hidden" name="oldImage" value="<?= htmlspecialchars($editProduct['imagePath']) ?>">
                    <div class="form-grid">
                        <div class="field">
                            <label for="edit_name"><?= $L['adminNameLabel'] ?></label>
                            <input class="input" type="text" id="edit_name" name="name"
                                   value="<?= htmlspecialchars($editProduct['name']) ?>"
                                   placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                        </div>
                        <div class="field">
                            <label for="edit_price"><?= $L['adminPriceLabel'] ?></label>
                            <input class="input" type="text" id="edit_price" name="price"
                                   value="<?= htmlspecialchars($editProduct['price']) ?>"
                                   placeholder="<?= $L['adminPricePlaceholder'] ?>"
                                   pattern="[0-9]+(\.\d{1,2})?$" required>
                        </div>
                        <div class="field field--full">
                            <label for="edit_description"><?= $L['adminDescLabel'] ?></label>
                            <textarea class="input" id="edit_description" name="description" rows="6"
                                      placeholder="<?= $L['adminDescPlaceholder'] ?>" required><?= htmlspecialchars($editProduct['description']) ?></textarea>
                        </div>
                        <div class="field">
                            <label for="product_image"><?= $L['adminImageLabel'] ?></label>
                            <input class="input" type="file" id="product_image" name="product_image"
                                   accept="image/jpeg,image/png,image/gif">
                            <span class="field-hint"><?= $L['adminImageHelp'] ?> <?= $L['adminImageHelpNotRequired'] ?></span>
                        </div>
                        <div class="field">
                            <label for="extra_images"><?= $L['adminGalleryLabel'] ?></label>
                            <input class="input" type="file" id="extra_images" name="extra_images[]"
                                   accept="image/jpeg,image/png,image/gif" multiple>
                            <span class="field-hint"><?= $L['adminGalleryHelp'] ?></span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--dark btn--lg"><?= $L['updateProduct'] ?></button>
                </form>

                <?php $galleryImages = $db->getGalleryImages($editProduct['product_id']); ?>
                <?php if (!empty($galleryImages)): ?>
                    <div class="gallery-manage">
                        <?php foreach ($galleryImages as $image): ?>
                            <div class="gallery-manage-item">
                                <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="" width="96" height="96">
                                <form method="post" class="js-confirm" data-confirm="<?= htmlspecialchars($L['adminDeleteConfirm'], ENT_QUOTES) ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                    <input type="hidden" name="delete_image_id" value="<?= (int)$image['id'] ?>">
                                    <input type="hidden" name="delete_image_product_id" value="<?= (int)$editProduct['product_id'] ?>">
                                    <input type="hidden" name="edit_id" value="<?= (int)$editProduct['product_id'] ?>">
                                    <button type="submit" class="btn btn--danger btn--sm"><?= $L['deleteProduct'] ?></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="panel">
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
                        <label for="product_image_create"><?= $L['adminImageLabel'] ?></label>
                        <input class="input" type="file" id="product_image_create" name="product_image"
                               accept="image/jpeg,image/png,image/gif" required>
                        <span class="field-hint"><?= $L['adminImageHelp'] ?></span>
                    </div>
                    <div class="field">
                        <label for="extra_images_create"><?= $L['adminGalleryLabel'] ?></label>
                        <input class="input" type="file" id="extra_images_create" name="extra_images[]"
                               accept="image/jpeg,image/png,image/gif" multiple>
                        <span class="field-hint"><?= $L['adminGalleryHelp'] ?></span>
                    </div>
                </div>
                <button type="submit" class="btn btn--blue btn--lg"><?= $L['adminCreateButton'] ?></button>
            </form>
        </section>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
