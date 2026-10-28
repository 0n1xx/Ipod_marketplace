<?php
    require_once __DIR__ . '/../includes/bootstrap.php';
    require_once __DIR__ . '/../includes/lang.php';
    require_once __DIR__ . '/_helpers.php';

    adminRequireAuth();

    $pageTitle = $L['createNewProduct'];
    $pageDesc  = $L['adminIntro'];

    $database = new Database();
    $db = new Product($database->connection);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $db->error[] = 'Invalid or expired form submission. Please try again.';
        } elseif (!empty($_FILES['product_image']['name'])) {
            $data = [
                'name'        => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'price'       => trim($_POST['price'] ?? ''),
                'imagePath'   => $_FILES['product_image'],
            ];

            $newProductId = $db->create($data);
            if ($newProductId) {
                if (!empty($_FILES['extra_images']['name'][0])) {
                    $db->addGalleryImages($newProductId, normalizeMultiFiles($_FILES['extra_images']));
                }
                header('Location: ' . BASE_URL . '/admin/?created=1');
                exit();
            }
        } else {
            $db->error[] = $L['adminImageHelp'];
        }
    }

    require_once __DIR__ . '/../templates/header.php';
?>
<main id="main">
    <div class="container admin-page">
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
                <h1 class="display display--md"><?= $L['createNewProduct'] ?></h1>
                <p class="body"><?= $L['adminIntro'] ?></p>
            </div>
            <a href="<?= BASE_URL ?>/admin/?lang=<?= $lang ?>" class="btn btn--outline"><?= $L['adminCancelEdit'] ?></a>
        </section>

        <section class="panel">
            <form class="form-stack" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <div class="form-grid">
                    <div class="field">
                        <label for="name"><?= $L['adminNameLabel'] ?></label>
                        <input class="input" type="text" id="name" name="name"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                    </div>
                    <div class="field">
                        <label for="price"><?= $L['adminPriceLabel'] ?></label>
                        <input class="input" type="text" id="price" name="price"
                               value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                               placeholder="<?= $L['adminPricePlaceholder'] ?>"
                               pattern="[0-9]+(\.\d{1,2})?$" required>
                    </div>
                    <div class="field field--full">
                        <label for="description"><?= $L['adminDescLabel'] ?></label>
                        <textarea class="input" id="description" name="description" rows="6"
                                  placeholder="<?= $L['adminDescPlaceholder'] ?>" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
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
                <div class="btn-row">
                    <button type="submit" class="btn btn--blue btn--lg"><?= $L['adminCreateButton'] ?></button>
                    <a href="<?= BASE_URL ?>/admin/?lang=<?= $lang ?>" class="btn btn--outline btn--lg"><?= $L['adminCancelEdit'] ?></a>
                </div>
            </form>
        </section>
    </div>
</main>
<?php require __DIR__ . '/../templates/footer.php'; ?>
