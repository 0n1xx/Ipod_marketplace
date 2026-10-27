<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    // Requiring all needed files for this page
    require_once __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/lang.php';
    require_once __DIR__ . '/includes/Session.php';
    require_once __DIR__ . '/includes/Database.php';
    require_once __DIR__ . '/admin/CrudProducts.php';
    require_once __DIR__ . '/includes/Csrf.php';

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
        }
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

        if ($db->create($data)) {
            $success = "Product created successfully!";
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
    <section class="intro-shop">
        <div>
            <h1><?= $L['adminWelcome'] ?></h1>
            <p><?= $L['adminIntro'] ?></p>
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
                            <button type="submit" class="btn-edit">
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
        <div>
            <h2 class="intro-form-admin">
                <?= $L['updateProduct'] ?>
            </h2>
        </div>
        <!-- The form with updating the information -->
        <section class="form-details">
            <form method="post" enctype="multipart/form-data">
                <fieldset>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                    <input type="hidden" name="update_id" value="<?= $editProduct['product_id'] ?>">
                    <input type="hidden" name="oldImage" value="<?= $editProduct['imagePath'] ?>">
                    <label for="name"><?= $L['adminNameLabel'] ?></label>
                    <input type="text" id="name" name="name"
                           placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                    <label for="description"><?= $L['adminDescLabel'] ?></label>
                    <textarea id="description" name="description" rows="10"
                              placeholder="<?= $L['adminDescPlaceholder'] ?>" required></textarea>
                    <label for="price"><?= $L['adminPriceLabel'] ?></label>
                    <input type="text" id="price" name="price"
                           placeholder="<?= $L['adminPricePlaceholder'] ?>"
                           pattern="[0-9]+(\.\d{1,2})?$" required>
                    <label for="product_image" class="custom-file-upload"><?= $L['adminImageLabel'] ?></label>
                    <input id="product_image" class="file-upload" type="file" name="product_image"
                           accept="image/jpeg,image/png,image/gif">
                    <small><?= $L['adminImageHelp'] ?> <?= $L['adminImageHelpNotRequired'] ?> </small>
                </fieldset>
                <div id="submit-button" class="admin-dash">
                    <button type="submit"><?= $L['updateProduct'] ?></button>
                </div>
            </form>
        </section>
    <?php endif; ?>
    <div>
        <h2 class="intro-form-admin">
            <?= $L['createNewProduct'] ?>
        </h2>
    </div>
    <!-- The form wih creating a new product -->
    <section class="form-details">
        <form method="post" enctype="multipart/form-data">
            <fieldset>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::token()) ?>">
                <label for="name"><?= $L['adminNameLabel'] ?></label>
                <input type="text" id="name" name="name"
                       placeholder="<?= $L['adminNamePlaceholder'] ?>" minlength="8" required>
                <label for="description"><?= $L['adminDescLabel'] ?></label>
                <textarea id="description" name="description" rows="10"
                          placeholder="<?= $L['adminDescPlaceholder'] ?>" required></textarea>
                <label for="price"><?= $L['adminPriceLabel'] ?></label>
                <input type="text" id="price" name="price"
                       placeholder="<?= $L['adminPricePlaceholder'] ?>"
                       pattern="[0-9]+(\.\d{1,2})?$" required>
                <label for="product_image" class="custom-file-upload"><?= $L['adminImageLabel'] ?></label>
                <input id="product_image" class="file-upload" type="file" name="product_image"
                       accept="image/jpeg,image/png,image/gif" required>
                <small><?= $L['adminImageHelp'] ?></small>
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