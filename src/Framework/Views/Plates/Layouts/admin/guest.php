<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$sessionUtility = Lapis::sessionUtility();
$alertTypes = ['success', 'info', 'warning', 'danger'];

/** @var string $nonce */
$nonce = $this->nonce_token ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->e($title ?? 'Admin Access') ?> - Lapis Orkestra</title>

  <!-- Icons -->
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/default/admin/images/lapis-apple-touch-icon.png"/>
  <link rel="icon" type="image/png" sizes="128x128" href="/assets/default/admin/images/lapis-icon-128.png"/>
  <link rel="icon" type="image/png" sizes="64x64" href="/assets/default/admin/images/lapis-icon-64.png"/>
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/default/admin/images/lapis-icon-32.png"/>
  <link rel="icon" type="image/x-icon" href="/assets/default/admin/images/lapis-icon-16.ico"/>

  <link rel="stylesheet" href="/assets/default/admin/css/bootstrap-theme.css?v=<?= time(); ?>">
</head>
<body class="d-flex align-items-center justify-content-center bg-body-secondary min-vh-100">

<div class="container">
  <div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-5">
      <!-- Flash Messages -->
      <div class="alerts-container mb-3">
        <?php foreach ($alertTypes as $type): ?>
          <?php foreach ((array) $sessionUtility->getAlert($type, []) as $message): ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
              <?= is_scalar($message) ? $this->e((string) $message) : ''; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>

      <!-- Section Content -->
      <main class="card shadow-sm p-4 bg-white rounded-4">
        <?= $this->section('content') ?>
      </main>

      <!-- Footer -->
      <footer class="pt-4 mt-5 border-top text-muted small text-center">
        <p>&copy; <?= date('Y') ?> <?= $this->brand_name ?? 'Lapis Orkestra' ?>. All rights reserved.</p>
        <img src="/assets/default/admin/images/lapis-logo.png" alt="Lapis Logo" style="height: 40px;">
      </footer>
    </div>
  </div>
</div>

<script src="/assets/default/shared/js/bootstrap.bundle.min.js"></script>
<script src="/assets/default/admin/js/default.js?v=<?= time(); ?>" nonce="<?= $nonce; ?>"></script>
</body>
</html>
