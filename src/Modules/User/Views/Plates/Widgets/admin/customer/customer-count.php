<?php declare(strict_types=1);

$stats ??= [
    'total_customers' => 0,
    'new_this_week' => 0,
];
?>

<div class="text-center">
  <h4 class="fw-bold mb-1"><?= $stats['total_customers'] ?></h4>
  <p class="text-muted mb-2">Total Customers</p>
  <small class="text-success">+<?= $stats['new_this_week'] ?> new this week</small>
</div>
