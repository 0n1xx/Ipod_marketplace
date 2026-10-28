<?php
/** @var array $order */
/** @var array $L */
/** @var string|null $orderAddressClass */
if (!Order::hasShippingAddress($order)) {
    return;
}
$addressClass = trim('order-address ' . ($orderAddressClass ?? ''));
?>
<div class="<?= htmlspecialchars($addressClass) ?>">
    <p class="eyebrow"><?= $L['shippingAddressLabel'] ?></p>
    <p><?= nl2br(htmlspecialchars(Order::formatShippingAddress($order))) ?></p>
</div>
<?php
unset($orderAddressClass);
?>
