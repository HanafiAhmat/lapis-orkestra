<?php declare(strict_types=1);

$stats ??= [
    'pending_email_verifications' => 0,
    'password_resets' => 0,
    'active_refresh_tokens' => 0,
    'revoked_tokens' => 0,
    'device_fingerprints' => 0,
    'mfa_enabled_users' => 0,
];
?>

<ul class="list-group list-group-flush small">
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-envelope-paper text-primary me-2"></i>Pending Email Verifications</span>
    <strong><?= $stats['pending_email_verifications'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-key text-warning me-2"></i>Password Reset Requests (7d)</span>
    <strong><?= $stats['password_resets'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-arrow-repeat text-info me-2"></i>Active Refresh Tokens (7d)</span>
    <strong><?= $stats['active_refresh_tokens'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-slash-circle text-danger me-2"></i>Revoked Tokens (7d)</span>
    <strong><?= $stats['revoked_tokens'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-phone text-secondary me-2"></i>Unique Device Fingerprints</span>
    <strong><?= $stats['device_fingerprints'] ?></strong>
  </li>
  <li class="list-group-item d-flex justify-content-between">
    <span><i class="bi bi-shield-lock text-success me-2"></i>MFA-Enabled Users</span>
    <strong><?= $stats['mfa_enabled_users'] ?></strong>
  </li>
</ul>
