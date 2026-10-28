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

    // PHP groups a multi-file <input name="x[]"> as $_FILES['x']['name'][0..n],
    // not as a list of per-file arrays; this reshapes it into the latter.
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

    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
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
<main class="admin-page">
    <section class="messageSuccessFail">
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
    </section>

    <section class="admin-topbar">
        <div>
            <p class="admin-eyebrow">QuébecIpod</p>
            <h1><?= $L['adminWelcome'] ?></h1>
            <p><?= $L['adminIntro'] ?></p>
        </div>
        <div class="admin-stats">
            <div class="admin-stat">
                <span class="admin-stat-value"><?= count($products) ?></span>
                <span class="admin-stat-label"><?= $L['adminStatTotal'] ?></span>
            </div>
            <div class="admin-stat admin-stat-available">
                <span class="admin-stat-value"><?= $availableCount ?></span>
                <span class="admin-stat-label"><?= $L['adminStatAvailable'] ?></span>
            </div>
            <div class="admin-stat admin-stat-sold">
                <span class="admin-stat-value"><?= $soldCount ?></span>
                <span class="admin-stat-label"><?= $L['adminStatSold'] ?></span>
            </div>
        </div>
    </section>

    <div class="admin-layout">
        <section class="admin-inventory" id="inventory">
            <div class="admin-section-header">
                <h2><?= $L['adminInventoryTitle'] ?></h2>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <p><?= $L['productsUnavailable'] ?></p>
                </div>
            <?php else: ?>
                <div class="admin-product-grid">
                    <?php foreach ($products as $product): ?>
                        <article class="admin-product <?= $product['is_sold'] ? 'is-sold' : '' ?>">
                            <div class="admin-product-media">
                                <?php if ($product['is_sold']): ?>
                                    <span class="sold-badge"><?= $L['soldBadge'] ?></span>
                                <?php endif; ?>
                                <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     loading="lazy" decoding="async">
                            </div>
                            <div class="admin-product-body">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="admin-product-desc"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                                <p class="admin-product-price">$<?= number_format((float)$product['price'], 2) ?></p>
                            </div>
                            <div class="update-delete">
                                <form method="post" class="action-button">
                                    <input type="hidden" name="edit_id" value="<?= $product['product_id'] ?>">
                                    <button type="submit" class="btn-edit"><?= $L['updateProduct'] ?></button>
                                </form>
                                <form method="post" class="action-button">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                    <input type="hidden" name="toggle_sold_id" value="<?= $product['product_id'] ?>">
                                    <button type="submit" class="btn-toggle">
                                        <?= $product['is_sold'] ? $L['markAvailable'] : $L['markSold'] ?>
                                    </button>
                                </form>
                                <form method="post" class="action-button" onsubmit="return confirm('<?= htmlspecialchars($L['adminDeleteConfirm'], ENT_QUOTES) ?>');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                    <input type="hidden" name="delete_id" value="<?= $product['product_id'] ?>">
                                    <button type="submit" class="btn-delete"><?= $L['deleteProduct'] ?></button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <aside class="admin-sidebar">
            <?php if ($editProduct): ?>
                <div class="admin-section-header">
                    <h2><?= $L['updateProduct'] ?></h2>
                    <a href="<?= BASE_URL ?>/admin/" class="admin-cancel-edit"><?= $L['adminCancelEdit'] ?></a>
                </div>
                <section class="form-details admin-form-details editing">
                    <p class="admin-editing-name"><?= htmlspecialchars($editProduct['name']) ?></p>
                    <form method="post" enctype="multipart/form-data">
                        <fieldset>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                            <input type="hidden" name="update_id" value="<?= $editProduct['product_id'] ?>">
                            <input type="hidden" name="oldImage" value="<?= htmlspecialchars($editProduct['imagePath']) ?>">
                            <div class="form-row">
                                <div>
                                    <label for="edit_name"><?= $L['adminNameLabel'] ?></label>
                                    <input type="text" id="edit_name" name="name" value="<?= htmlspecialchars($editProduct['name']) ?>"
                                           placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                                </div>
                                <div>
                                    <label for="edit_price"><?= $L['adminPriceLabel'] ?></label>
                                    <input type="text" id="edit_price" name="price" value="<?= htmlspecialchars($editProduct['price']) ?>"
                                           placeholder="<?= $L['adminPricePlaceholder'] ?>"
                                           pattern="[0-9]+(\.\d{1,2})?$" required>
                                </div>
                            </div>
                            <label for="edit_description"><?= $L['adminDescLabel'] ?></label>
                            <textarea id="edit_description" name="description" rows="8"
                                      placeholder="<?= $L['adminDescPlaceholder'] ?>" required><?= htmlspecialchars($editProduct['description']) ?></textarea>
                            <div class="form-row form-row-files">
                                <div>
                                    <label for="product_image" class="custom-file-upload"><?= $L['adminImageLabel'] ?></label>
                                    <input id="product_image" class="file-upload" type="file" name="product_image"
                                           accept="image/jpeg,image/png,image/gif">
                                    <small><?= $L['adminImageHelp'] ?> <?= $L['adminImageHelpNotRequired'] ?></small>
                                </div>
                                <div>
                                    <label for="extra_images" class="custom-file-upload"><?= $L['adminGalleryLabel'] ?></label>
                                    <input id="extra_images" class="file-upload" type="file" name="extra_images[]"
                                           accept="image/jpeg,image/png,image/gif" multiple>
                                    <small><?= $L['adminGalleryHelp'] ?></small>
                                </div>
                            </div>
                        </fieldset>
                        <div id="submit-button" class="admin-dash">
                            <button type="submit"><?= $L['updateProduct'] ?></button>
                        </div>
                    </form>
                </section>

                <?php $galleryImages = $db->getGalleryImages($editProduct['product_id']); ?>
                <?php if (!empty($galleryImages)): ?>
                    <section class="gallery-manage">
                        <?php foreach ($galleryImages as $image): ?>
                            <div class="gallery-manage-item">
                                <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="<?= htmlspecialchars($editProduct['name']) ?>">
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                    <input type="hidden" name="delete_image_id" value="<?= $image['id'] ?>">
                                    <input type="hidden" name="delete_image_product_id" value="<?= $editProduct['product_id'] ?>">
                                    <input type="hidden" name="edit_id" value="<?= $editProduct['product_id'] ?>">
                                    <button type="submit" class="btn-delete"><?= $L['deleteProduct'] ?></button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>
            <?php endif; ?>

            <div class="admin-section-header">
                <h2><?= $L['createNewProduct'] ?></h2>
            </div>
            <section class="form-details admin-form-details">
                <form method="post" enctype="multipart/form-data">
                    <fieldset>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                        <div class="form-row">
                            <div>
                                <label for="name"><?= $L['adminNameLabel'] ?></label>
                                <input type="text" id="name" name="name"
                                       placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                            </div>
                            <div>
                                <label for="price"><?= $L['adminPriceLabel'] ?></label>
                                <input type="text" id="price" name="price"
                                       placeholder="<?= $L['adminPricePlaceholder'] ?>"
                                       pattern="[0-9]+(\.\d{1,2})?$" required>
                            </div>
                        </div>
                        <label for="description"><?= $L['adminDescLabel'] ?></label>
                        <textarea id="description" name="description" rows="8"
                                  placeholder="<?= $L['adminDescPlaceholder'] ?>" required></textarea>
                        <div class="form-row form-row-files">
                            <div>
                                <label for="product_image_create" class="custom-file-upload"><?= $L['adminImageLabel'] ?></label>
                                <input id="product_image_create" class="file-upload" type="file" name="product_image"
                                       accept="image/jpeg,image/png,image/gif" required>
                                <small><?= $L['adminImageHelp'] ?></small>
                            </div>
                            <div>
                                <label for="extra_images_create" class="custom-file-upload"><?= $L['adminGalleryLabel'] ?></label>
                                <input id="extra_images_create" class="file-upload" type="file" name="extra_images[]"
                                       accept="image/jpeg,image/png,image/gif" multiple>
                                <small><?= $L['adminGalleryHelp'] ?></small>
                            </div>
                        </div>
                    </fieldset>
                    <div id="submit-button" class="admin-dash">
                        <button type="submit"><?= $L['adminCreateButton'] ?></button>
                    </div>
                </form>
            </section>
        </aside>
    </div>

    <div class="log-out-button-div">
        <a href="<?= BASE_URL ?>/logout.php" class="log-out-button"><?= $L['logOut'] ?></a>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
