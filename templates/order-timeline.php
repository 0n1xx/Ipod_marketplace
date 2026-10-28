<?php
/** @var string $status */
/** @var array $L */
$status = Order::normalizeStatus($status ?? 'paid');
$steps = Order::STATUSES;
$currentIndex = array_search($status, $steps, true);
if ($currentIndex === false) {
    $currentIndex = 0;
}
?>
<ol class="status-timeline" aria-label="<?= htmlspecialchars($L['orderStatusLabel']) ?>">
    <?php foreach ($steps as $i => $step): ?>
        <?php
            $state = 'is-todo';
            if ($i < $currentIndex) {
                $state = 'is-done';
            } elseif ($i === $currentIndex) {
                $state = 'is-current';
            }
        ?>
        <li class="status-timeline__step <?= $state ?>">
            <span class="status-timeline__dot" aria-hidden="true"></span>
            <span class="status-timeline__label"><?= $L['orderStatus_' . $step] ?></span>
        </li>
    <?php endforeach; ?>
</ol>
