<?php declare(strict_types=1);

$stats ??= [
    'active_sessions' => 0,
    'new_sessions_7d' => 0,
    'revoked_sessions_7d' => 0,
];
?>

<ul class="list-group list-group-flush small">
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-person-check-fill text-primary me-2"></i>Active Sessions</span>
    <strong><?= $stats['active_sessions'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-person-plus-fill text-success me-2"></i>New Sessions (7d)</span>
    <strong><?= $stats['new_sessions_7d'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-person-dash-fill text-danger me-2"></i>Revoked Sessions (7d)</span>
    <strong><?= $stats['revoked_sessions_7d'] ?></strong>
  </li>
</ul>
