<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/** @var string $brandName */
$brandName = Lapis::configRegistry()->get('app.name') ?? 'Lapis Orkestra';

/** @var string $cdnUrl  Base URL for CDN assets (images, styles) */
$cdnUrl = Lapis::configRegistry()->get('app.cdn_url') ?? Lapis::requestUtility()->getCurrentAppBaseUrl();

/** @var string $frontendUrl  Frontend home URL */
$frontendUrl = Lapis::configRegistry()->get('app.frontend_url') ?? Lapis::requestUtility()->getCurrentAppBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $brandName; ?></title>
</head>
<body class="email-body">
  <div class="email-container">
    <!-- Header -->
    <div class="email-header">
      <h1><?= $brandName; ?></h1>
    </div>

    <!-- Content -->
    <div class="email-content">
      <?= $this->section('content') ?>
    </div>

    <!-- Footer -->
    <div class="email-footer">
      <p>&copy; <?= date('Y') ?> <a href="<?= $frontendUrl; ?>"><?= $brandName; ?></a>. All rights reserved.</p>
      <img src="<?= $cdnUrl ?>/assets/default/public//images/lapis-logo.png" alt="<?= $brandName; ?> Logo" class="email-logo">
    </div>
  </div>
</body>
</html>
