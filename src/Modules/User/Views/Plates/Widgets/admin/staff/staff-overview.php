<?php declare(strict_types=1);

$stats ??= [
    'total_staff' => 0,
    'active_staff' => 0,
    'inactive_staff' => 0,
];
?>

<ul class="list-group list-group-flush small">
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-people-fill text-primary me-2"></i>Total Staff</span>
    <strong><?= $stats['total_staff'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-person-check text-success me-2"></i>Active Staff</span>
    <strong><?= $stats['active_staff'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-person-x text-danger me-2"></i>Inactive Staff</span>
    <strong><?= $stats['inactive_staff'] ?></strong>
  </li>
</ul>
