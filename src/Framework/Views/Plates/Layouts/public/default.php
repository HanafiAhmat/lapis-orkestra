<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Entities\User;
use BitSynama\Lapis\Framework\DTO\MenuItemDefinition;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/** @var User $user */
$user = Lapis::varRegistry()->getOrSkip('user');

$sessionUtility = Lapis::sessionUtility();

/** @var string $csrf_token */
$csrf_token = $sessionUtility->getCsrfToken();

/** @var string $nonce */
$nonce = $this->nonce_token ?? '';

/** @var string $brandName */
$brandName = $this->brand_name ?? 'Lapis Orkestra';

$alertTypes = ['success', 'info', 'warning', 'danger'];

/** @var array<string, mixed> $mainMenuRaw */
$mainMenuRaw = Lapis::publicMenuRegistry()->getBySection('main');

/** @var array<int, MenuItemDefinition> $mainMenu */
$mainMenu = [];
if (is_array($mainMenuRaw)) {
    // trust only MenuItemDefinition items
    foreach ($mainMenuRaw as $item) {
        if ($item instanceof MenuItemDefinition) {
            $mainMenu[] = $item;
        }
    }
} elseif ($mainMenuRaw instanceof Traversable) {
    foreach ($mainMenuRaw as $item) {
        if ($item instanceof MenuItemDefinition) {
            $mainMenu[] = $item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $this->e($title ?? 'Welcome'); ?></title>

  <!-- Icons -->
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/default/public/images/lapis-apple-touch-icon.png"/>
  <link rel="icon" type="image/png" sizes="128x128" href="/assets/default/public/images/lapis-icon-128.png"/>
  <link rel="icon" type="image/png" sizes="64x64" href="/assets/default/public/images/lapis-icon-64.png"/>
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/default/public/images/lapis-icon-32.png"/>
  <link rel="icon" type="image/x-icon" href="/assets/default/public/images/favicon.ico"/>

  <link rel="stylesheet" href="/assets/default/public/css/bootstrap-theme.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="/assets/default/public/css/default.css?v=<?= time(); ?>">
</head>
<body class="bg-light text-dark d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold text-primary" href="/">Lapis Orkestra</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <?php foreach ($mainMenu as $firstlevelItem): ?>
            <?php if ($firstlevelItem->children === []): ?>
              <li class="nav-item">
                <a class="nav-link"
                   href="<?= $this->e($firstlevelItem->href ?? '#'); ?>">
                  <?= $this->e($firstlevelItem->label); ?>
                </a>
              </li>
            <?php else: ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <?= $this->e($firstlevelItem->label); ?>
                </a>
                <ul class="dropdown-menu">
                  <?php if (is_array($firstlevelItem->children)): ?>
                    <?php foreach ($firstlevelItem->children as $item): ?>
                      <li>
                        <a class="dropdown-item"
                           href="<?= $this->e($item->href ?? '#'); ?>">
                          <?= $this->e($item->label); ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
        <ul class="navbar-nav">
          <?php if (! empty($user)): ?>
            <li class="nav-item me-2">
              <span class="navbar-text">Hi, <?= $this->e($user->name ?? 'User'); ?></span>
            </li>
            <li class="nav-item">
              <form method="post" action="/auth/logout" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
              </form>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/auth/login">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Alerts -->
  <div class="container mt-3">
    <div class="alerts-container">
      <?php foreach ($alertTypes as $type): ?>
        <?php $messages = $sessionUtility->getAlert($type, []); ?>
        <?php if (! empty($messages)): ?>
          <?php foreach ((array) $messages as $message): ?>
            <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
              <?= is_scalar($message) ? $this->e((string) $message) : ''; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Main -->
  <main class="flex-grow-1 container py-4">
    <?= $this->section('content'); ?>
  </main>

  <!-- Footer -->
  <footer class="bg-white border-top mt-auto py-3 text-center">
    <div class="container">
      <p class="mb-0">&copy; <?= date('Y'); ?> <?= $this->e($brandName); ?>. All rights reserved.</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="/assets/default/shared/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/default/public/js/default.js?v=<?= time(); ?>" nonce="<?= $nonce; ?>"></script>
</body>
</html>
