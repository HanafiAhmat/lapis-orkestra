<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 * @var string|null $title
 */

// helper for escaping even when Plates $this is not visible to PHPStan
$esc = (static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));

$pageTitle = (isset($title) && is_string($title) && $title !== '') ? $title : 'Admin Access';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $esc($pageTitle) ?> - Lapis Orkestra</title>

  <!-- Icons -->
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/default/admin/images/lapis-apple-touch-icon.png"/>
  <link rel="icon" type="image/png" sizes="128x128" href="/assets/default/admin/images/lapis-icon-128.png"/>
  <link rel="icon" type="image/png" sizes="64x64" href="/assets/default/admin/images/lapis-icon-64.png"/>
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/default/admin/images/lapis-icon-32.png"/>
  <link rel="icon" type="image/x-icon" href="/assets/default/admin/images/lapis-icon-16.ico"/>

  <link rel="stylesheet" href="/assets/default/admin/css/bootstrap-theme.css?v=<?= (string) time(); ?>">
</head>
<body class="d-flex align-items-center justify-content-center bg-body-secondary min-vh-100">

  <?= $this->section('content') ?>

<script src="/assets/default/shared/js/bootstrap.bundle.min.js"></script>

<?php
$nonce = '';
if (isset($this->nonce_token) && is_string($this->nonce_token)) {
    $nonce = $this->nonce_token;
}
?>
<script src="/assets/default/admin/js/default.js?v=<?= (string) time(); ?>" nonce="<?= $esc($nonce) ?>"></script>
</body>
</html>
