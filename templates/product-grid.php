<?php
/** @var array $products */
/** @var array $L */
/** @var string $lang */
$showSoldBadge = !empty($showSoldBadge);
?>
<ul class="product-grid">
    <?php foreach ($products as $product): ?>
        <li class="product-card<?= $showSoldBadge ? ' is-sold' : '' ?>">
            <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['product_id'] ?>&lang=<?= $lang ?>">
                <div class="tile">
                    <?php if ($showSoldBadge): ?>
                        <span class="sold-badge"><?= $L['soldBadge'] ?></span>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                </div>
                <div class="meta">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="price">
                        <?= $showSoldBadge
                            ? $L['soldBadge']
                            : '$' . number_format((float)$product['price'], 2) ?>
                    </p>
                </div>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
