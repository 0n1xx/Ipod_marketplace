<?php
/** @var array $products */
/** @var array $L */
/** @var string $lang */
$showSoldBadge = !empty($showSoldBadge);
?>
<ul class="product-grid" data-product-grid>
    <?php foreach ($products as $i => $product): ?>
        <li class="product-card<?= $showSoldBadge ? ' is-sold' : '' ?>" style="--d: <?= (int)($i * 40) ?>ms" data-reveal>
            <a href="<?= BASE_URL ?>/product.php?id=<?= (int)$product['product_id'] ?>&lang=<?= $lang ?>">
                <div class="tile">
                    <span class="skeleton-shine" aria-hidden="true"></span>
                    <?php if ($showSoldBadge): ?>
                        <span class="sold-badge"><?= $L['soldBadge'] ?></span>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars($product['imagePath']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         loading="lazy"
                         data-skeleton-img>
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
