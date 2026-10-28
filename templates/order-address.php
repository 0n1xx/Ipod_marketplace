<?php
/** @var array $order */
/** @var array $L */
if (!Order::hasShippingAddress($order)) {
    return;
}
?>
<div class="order-address">
    <p class="eyebrow"><?= $L['shippingAddressLabel'] ?></p>
    <p><?= nl2br(htmlspecialchars(Order::formatShippingAddress($order))) ?></p>
</div>
