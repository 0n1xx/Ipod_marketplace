<?php
    // Requiring all needed files for this page
    require_once __DIR__ . '/includes/bootstrap.php';
    require_once __DIR__ . '/includes/lang.php';

    // Only logged-in admins may reach this page; anyone else is redirected away
    Session::start();
    if (!Session::isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
    if (Session::get("is_admin") != 1) {
        header("Location: personal-account.php");
        exit();
    }

    require_once __DIR__ . '/templates/header.php';
    $pageTitle = $L['shopTitle'];
    $pageDesc  = $L['shopDesc'];
    $success = null;

    // Connecting to the database
    $database = new Database();
    $db = new CrudProducts($database->connection);

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

    // Any POST request (create/update/delete) must carry a valid CSRF token
    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
    if ($isPost && !Csrf::verify($_POST['csrf_token'] ?? null)) {
        $db->error[] = "Invalid or expired form submission. Please try again.";
        $isPost = false;
    }

    // Handling the update procedure
    if ($isPost && isset($_POST['update_id'])) {

        $updateId = (int)$_POST['update_id'];
        $product = $db->getProductById($updateId);

        $data = [
            "name" => trim($_POST["name"]),
            "description" => trim($_POST["description"]),
            "price" => trim($_POST["price"]),
            "oldImage" => $product['imagePath'],
            "newImage" => $_FILES['product_image']
        ];

        if ($db->update($updateId, $data)) {
            $success = "Product updated successfully!";
            $editProduct = null;

            if (!empty($_FILES['extra_images']['name'][0])) {
                $db->addGalleryImages($updateId, normalizeMultiFiles($_FILES['extra_images']));
            }
        }
    }

    // Handling the process when a user deletes one gallery photo
    // (the form also submits edit_id so the edit view reopens below)
    elseif ($isPost && isset($_POST['delete_image_id'])) {
        $db->deleteGalleryImage((int)$_POST['delete_image_id'], (int)$_POST['delete_image_product_id']);
    }

    // Handling the process when a user toggles a product's sold status
    elseif ($isPost && isset($_POST['toggle_sold_id'])) {
        $toggleId = (int)$_POST['toggle_sold_id'];
        $product = $db->getProductById($toggleId);
        if ($product && $db->setSold($toggleId, !$product['is_sold'])) {
            $success = $product['is_sold'] ? "Product marked as available!" : "Product marked as sold!";
        }
    }

    // Handling the process when a user wants to delete products
    elseif ($isPost && isset($_POST['delete_id'])) {
        $deleteId = (int)$_POST['delete_id'];

        $product = $db->getProductById($deleteId);
        if ($db->delete($deleteId)) {
            if ($product && file_exists(__DIR__ . '/../' . $product['imagePath'])) {
                unlink(__DIR__ . '/../' . $product['imagePath']);
            }
            $success = "Product deleted successfully!";
        }
    }

    // Handling the creation of new products
    elseif ($isPost && !empty($_FILES['product_image']['name'])) {

        $name = trim($_POST["name"]);
        $description = trim($_POST["description"]);
        $price = trim($_POST["price"]);
        $imageFile = $_FILES["product_image"];

        $data = [
            "name" => $name,
            "description" => $description,
            "price" => $price,
            "imagePath" => $imageFile
        ];

        $newProductId = $db->create($data);
        if ($newProductId) {
            $success = "Product created successfully!";

            if (!empty($_FILES['extra_images']['name'][0])) {
                $db->addGalleryImages($newProductId, normalizeMultiFiles($_FILES['extra_images']));
            }
        }
    }

    // reading all the products
    $products = $db->read();

    $editProduct = null;
    if (isset($_POST['edit_id'])) {
        $editId = (int)$_POST['edit_id'];
        $editProduct = $db->getProductById($editId);
    }
?>
<main>
    <!-- A success or fail section -->
    <section class="messageSuccessFail">
        <?php if ($success): ?>
            <div class="alert_success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
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
            <h1><?= $L['adminWelcome'] ?></h1>
            <p><?= $L['adminIntro'] ?></p>
        </div>
        <div class="admin-stats">
            <div class="admin-stat">
                <span class="admin-stat-value"><?= count($products) ?></span>
                <span class="admin-stat-label"><?= $L['adminStatTotal'] ?></span>
            </div>
            <div class="admin-stat">
                <span class="admin-stat-value"><?= count(array_filter($products, fn($p) => $p['is_sold'])) ?></span>
                <span class="admin-stat-label"><?= $L['adminStatSold'] ?></span>
            </div>
        </div>
    </section>
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <p><?= $L['productsUnavailable'] ?></p>
        </div>
    <?php else: ?>
    <!-- A dynamic section product which displays all available products -->
    <section class="product-section">
            <?php foreach ($products as $product): ?>
            <div>
                <div class="product-card">
                    <?php if ($product['is_sold']): ?>
                        <span class="sold-badge"><?= $L['soldBadge'] ?></span>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         loading="lazy" decoding="async">
                    <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="product-description">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>
                    <p class="product-price">
                        $<?= number_format((float)$product['price'], 2) ?>
                    </p>
                </div>
                <div class="update-delete">
                        <form method="post" class="action-button">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                            <input type="hidden" name="toggle_sold_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="btn-toggle">
                                <?= $product['is_sold'] ? $L['markAvailable'] : $L['markSold'] ?>
                            </button>
                        </form>
                        <form method="post" class="action-button">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                            <input type="hidden" name="delete_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="btn-delete"><?= $L['deleteProduct'] ?></button>
                        </form>
                        <form method="post" class="action-button">
                            <input type="hidden" name="edit_id" value="<?= $product['product_id'] ?>">
                            <button type="submit" class="btn-edit"><?= $L['updateProduct'] ?></button>
                        </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
    <!-- Showing this form only if an edit button is clicked -->
    <?php if ($editProduct): ?>
        <div class="admin-section-header">
            <h2><?= $L['updateProduct'] ?>: <?= htmlspecialchars($editProduct['name']) ?></h2>
            <a href="admin-dashboard.php" class="admin-cancel-edit"><?= $L['adminCancelEdit'] ?></a>
        </div>
        <!-- The form with updating the information -->
        <section class="form-details admin-form-details editing">
            <form method="post" enctype="multipart/form-data">
                <fieldset>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <input type="hidden" name="update_id" value="<?= $editProduct['product_id'] ?>">
                    <input type="hidden" name="oldImage" value="<?= $editProduct['imagePath'] ?>">
                    <div class="form-row">
                        <div>
                            <label for="name"><?= $L['adminNameLabel'] ?></label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($editProduct['name']) ?>"
                                   placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                        </div>
                        <div>
                            <label for="price"><?= $L['adminPriceLabel'] ?></label>
                            <input type="text" id="price" name="price" value="<?= htmlspecialchars($editProduct['price']) ?>"
                                   placeholder="<?= $L['adminPricePlaceholder'] ?>"
                                   pattern="[0-9]+(\.\d{1,2})?$" required>
                        </div>
                    </div>
                    <label for="description"><?= $L['adminDescLabel'] ?></label>
                    <textarea id="description" name="description" rows="10"
                              placeholder="<?= $L['adminDescPlaceholder'] ?>" required><?= htmlspecialchars($editProduct['description']) ?></textarea>
                    <div class="form-row">
                        <div>
                            <label for="product_image" class="custom-file-upload"><?= $L['adminImageLabel'] ?></label>
                            <input id="product_image" class="file-upload" type="file" name="product_image"
                                   accept="image/jpeg,image/png,image/gif">
                            <small><?= $L['adminImageHelp'] ?> <?= $L['adminImageHelpNotRequired'] ?> </small>
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
        <!-- Existing gallery photos, each removable on its own -->
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
    <!-- The form wih creating a new product -->
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
                <textarea id="description" name="description" rows="10"
                          placeholder="<?= $L['adminDescPlaceholder'] ?>" required></textarea>
                <div class="form-row">
                    <div>
                        <label for="product_image" class="custom-file-upload"><?= $L['adminImageLabel'] ?></label>
                        <input id="product_image" class="file-upload" type="file" name="product_image"
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
    <!-- A div with the log-out button -->
    <div class="log-out-button-div">
        <a href="logout.php" class="log-out-button"><?php echo $L['logOut'];?></a>
    </div>
</main>
<?php require './templates/footer.php'; ?>