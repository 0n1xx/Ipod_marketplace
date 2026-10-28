<?php
/** @var array $order */
/** @var array $L */
$items = $order['items'] ?? [];
if ($items === []) {
    return;
}
$compact = !empty($orderItemsCompact);
$listClass = $compact ? 'order-items order-items--compact' : 'order-items';
$imgSize = $compact ? 40 : 72;
?>
<ul class="<?= $listClass ?>">
    <?php foreach ($items as $item): ?>
        <li>
            <?php if (!empty($item['imagePath'])): ?>
                <img src="<?= htmlspecialchars($item['imagePath']) ?>"
                     alt="<?= $compact ? '' : htmlspecialchars($item['name']) ?>"
                     width="<?= $imgSize ?>" height="<?= $imgSize ?>" loading="lazy">
            <?php endif; ?>
            <span><?= htmlspecialchars($item['name']) ?></span>
        </li>
    <?php endforeach; ?>
</ul>
<?php
unset($orderItemsCompact);
?>
