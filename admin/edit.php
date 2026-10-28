<?php
    require_once __DIR__ . '/../includes/bootstrap.php';
    require_once __DIR__ . '/../includes/lang.php';
    require_once __DIR__ . '/_helpers.php';

    adminRequireAuth();

    $database = new Database();
    $db = new Product($database->connection);
    $success = null;

    $productId = (int)($_GET['id'] ?? $_POST['update_id'] ?? 0);
    $product = $productId > 0 ? $db->getProductById($productId) : null;

    if (!$product) {
        header('Location: ' . BASE_URL . '/admin/');
        exit();
    }

    $pageTitle = $L['updateProduct'] . ' — ' . $product['name'];
    $pageDesc  = $L['adminIntro'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $db->error[] = 'Invalid or expired form submission. Please try again.';
        } elseif (isset($_POST['delete_image_id'])) {
            $db->deleteGalleryImage((int)$_POST['delete_image_id'], $productId);
            $success = 'Gallery photo removed.';
            $product = $db->getProductById($productId);
        } elseif (isset($_POST['update_id'])) {
            $data = [
                'name'        => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'price'       => trim($_POST['price'] ?? ''),
                'oldImage'    => $product['imagePath'],
                'newImage'    => $_FILES['product_image'] ?? ['name' => ''],
            ];

            if ($db->update($productId, $data)) {
                if (!empty($_FILES['extra_images']['name'][0])) {
                    $db->addGalleryImages($productId, normalizeMultiFiles($_FILES['extra_images']));
                }
                header('Location: ' . BASE_URL . '/admin/?updated=1');
                exit();
            }
            $product = $db->getProductById($productId) ?: $product;
        }
    }

    $galleryImages = $db->getGalleryImages($productId);
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
                <p class="eyebrow"><?= $L['adminInventoryTitle'] ?></p>
                <h1 class="display display--md"><?= $L['updateProduct'] ?></h1>
                <p class="body"><?= htmlspecialchars($product['name']) ?></p>
            </div>
            <a href="<?= BASE_URL ?>/admin/?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['adminCancelEdit'] ?></a>
        </section>

        <section class="panel">
            <div class="edit-preview">
                <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>" width="240" height="240">
                <div>
                    <p class="eyebrow"><?= $product['is_sold'] ? $L['soldBadge'] : $L['adminStatAvailable'] ?></p>
                    <p class="price-lg">$<?= number_format((float)$product['price'], 2) ?></p>
                </div>
            </div>

            <form class="form-stack" method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/admin/edit.php?id=<?= $productId ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <input type="hidden" name="update_id" value="<?= $productId ?>">
                <div class="form-grid">
                    <div class="field">
                        <label for="edit_name"><?= $L['adminNameLabel'] ?></label>
                        <input class="input" type="text" id="edit_name" name="name"
                               value="<?= htmlspecialchars($product['name']) ?>"
                               placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                    </div>
                    <div class="field">
                        <label for="edit_price"><?= $L['adminPriceLabel'] ?></label>
                        <input class="input" type="text" id="edit_price" name="price"
                               value="<?= htmlspecialchars($product['price']) ?>"
                               placeholder="<?= $L['adminPricePlaceholder'] ?>"
                               pattern="[0-9]+(\.\d{1,2})?$" required>
                    </div>
                    <div class="field field--full">
                        <label for="edit_description"><?= $L['adminDescLabel'] ?></label>
                        <textarea class="input" id="edit_description" name="description" rows="8"
                                  placeholder="<?= $L['adminDescPlaceholder'] ?>" required><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    <div class="field">
                        <span class="label"><?= $L['adminImageLabel'] ?></span>
                        <label for="product_image" class="dropzone">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4"/><path d="M7 9l5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                            <strong><?= $L['adminImageLabel'] ?></strong>
                            <span><?= $L['adminImageHelp'] ?> <?= $L['adminImageHelpNotRequired'] ?></span>
                        </label>
                        <input class="sr-only" type="file" id="product_image" name="product_image"
                               accept="image/jpeg,image/png,image/gif" data-dropzone-input>
                    </div>
                    <div class="field">
                        <span class="label"><?= $L['adminGalleryLabel'] ?></span>
                        <label for="extra_images" class="dropzone">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="M7 3h12a2 2 0 0 1 2 2v12"/><path d="M3 15l4-4 4 4"/></svg>
                            <strong><?= $L['adminGalleryLabel'] ?></strong>
                            <span><?= $L['adminGalleryHelp'] ?></span>
                        </label>
                        <input class="sr-only" type="file" id="extra_images" name="extra_images[]"
                               accept="image/jpeg,image/png,image/gif" multiple data-dropzone-input>
                    </div>
                </div>
                <div class="btn-row">
                    <button type="submit" class="btn btn--dark btn--lg"><?= $L['updateProduct'] ?></button>
                    <a href="<?= BASE_URL ?>/admin/?lang=<?= $lang ?>" class="btn btn--outline btn--lg"><?= $L['adminCancelEdit'] ?></a>
                </div>
            </form>
        </section>

        <?php if (!empty($galleryImages)): ?>
            <section class="panel">
                <div class="panel-head">
                    <h2><?= $L['adminGalleryLabel'] ?></h2>
                </div>
                <div class="gallery-manage">
                    <?php foreach ($galleryImages as $image): ?>
                        <div class="gallery-manage-item">
                            <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="" width="96" height="96">
                            <form method="post" class="js-confirm" data-confirm="<?= htmlspecialchars($L['adminDeleteConfirm'], ENT_QUOTES) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                                <input type="hidden" name="delete_image_id" value="<?= (int)$image['id'] ?>">
                                <button type="submit" class="btn btn--danger btn--sm"><?= $L['deleteProduct'] ?></button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
