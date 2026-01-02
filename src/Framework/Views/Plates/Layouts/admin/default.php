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

/** @var string $csrfToken */
$csrfToken = $sessionUtility->getCsrfToken();

$alertTypes = ['success', 'info', 'warning', 'danger'];

/** @var string $adminPrefix */
$adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

/** @var string $currentUrl */
$currentUrl = Lapis::requestUtility()->getCurrentUrl();

$createAction ??= null;

/** @var string $nonce */
$nonce = $this->nonce_token ?? '';

/** @var array<string, mixed> $mainMenuRaw */
$mainMenuRaw = Lapis::adminMenuRegistry()->getBySection('main');

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
  <title><?= $this->e($title ?? 'Dashboard') ?> - Admin Panel</title>

  <!-- Icons -->
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/default/admin/images/lapis-apple-touch-icon.png"/>
  <link rel="icon" type="image/png" sizes="128x128" href="/assets/default/admin/images/lapis-icon-128.png"/>
  <link rel="icon" type="image/png" sizes="64x64" href="/assets/default/admin/images/lapis-icon-64.png"/>
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/default/admin/images/lapis-icon-32.png"/>
  <link rel="icon" type="image/x-icon" href="/assets/default/admin/images/lapis-icon-16.ico"/>

  <!-- EasyMDE from CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">

  <!-- Styles -->
  <link rel="stylesheet" href="/assets/default/admin/css/bootstrap-theme.css?v=<?= time(); ?>">
</head>
<body class="bg-body-secondary">

<div class="d-flex flex-column min-vh-100">

  <!-- Topbar -->
  <nav class="navbar navbar-expand-md navbar-dark bg-dark px-3">
    <a class="navbar-brand text-info fw-bold" href="<?= $adminPrefix ?>">Admin Panel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="ms-auto d-flex align-items-center">
      <span class="text-white me-3">Logged in as: <strong><?= $this->e($user->name ?? '') ?></strong></span>
      <form method="post" action="<?= $adminPrefix ?>/auth/logout" class="d-inline">
        <input type="hidden" name="csrf_token" value="<?= $this->e($csrfToken) ?>">
        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
      </form>
    </div>
  </nav>

  <div class="container-fluid flex-grow-1">
    <div class="row">
      <!-- Sidebar -->
      <nav id="adminSidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
        <div class="position-sticky pt-3 text-white vh-100">
          <?php foreach ($mainMenu as $firstlevelItem): ?>
            <h6 class="text-uppercase text-white small mt-4"><?= $this->e($firstlevelItem->label) ?></h6>
            <ul class="nav flex-column mb-2">
              <?php if (! empty($firstlevelItem->href)): ?>
                <li class="nav-item">
                  <a class="nav-link text-white" href="<?= $firstlevelItem->href ?>">
                    <i class="bi <?= $firstlevelItem->icon ?? 'bi-circle' ?> me-1"></i> <?= $this->e($firstlevelItem->label) ?>
                  </a>
                </li>
              <?php endif; ?>
              <?php if (is_array($firstlevelItem->children)): ?>
                <?php foreach ($firstlevelItem->children as $item): ?>
                  <li class="nav-item">
                    <a class="nav-link text-white" href="<?= $item->href ?>">
                      <i class="bi <?= $item->icon ?? 'bi-circle' ?> me-1"></i> <?= $this->e($item->label) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          <?php endforeach; ?>
        </div>
      </nav>

      <!-- Main content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

        <div class="w-100">
          <h1 class="h4 mb-3 w-75 d-inline"><?= $this->e($title ?? 'Dashboard') ?></h1>
          <div class="w-25 d-inline">
            <?php if (! empty($backAction)): ?>
              <a href="<?= $backAction; ?>" class="btn btn-danger btn-sm float-end ms-2"><i class="bi bi-arrow-90deg-left me-1"></i> Back</a>
            <?php endif; ?>
            <?php if (! empty($createAction)): ?>
              <a href="<?= $createAction; ?>" class="btn btn-primary btn-sm float-end"><i class="bi bi-plus-circle me-1"></i> Add New</a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Alerts -->
        <div class="alerts-container mt-3 mb-3">
          <?php foreach ($alertTypes as $type): ?>
            <?php foreach ((array) $sessionUtility->getAlert($type, []) as $message): ?>
              <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
                <?= is_scalar($message) ? $this->e((string) $message) : ''; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </div>

        <?= $this->section('content') ?>

        <!-- Footer -->
        <footer class="pt-4 mt-5 border-top text-muted small text-center">
          <p>&copy; <?= date(
              'Y'
          ) ?> <?= $this->brand_name ?? 'Lapis Orkestra' ?>. All rights reserved.</p>
          <img src="/assets/default/admin/images/lapis-logo.png" alt="Lapis Logo" style="height: 40px;">
        </footer>

        <!-- Modal for Deletion -->
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-1"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                Are you sure you want to delete <strong id="deleteRecordName"></strong>?
                <p class="text-muted small mb-0">This action cannot be undone.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelDeleteBtn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Toasts -->
        <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

      </main>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="/assets/default/shared/js/bootstrap.bundle.min.js"></script>
<script src="/assets/default/admin/js/default.js?v=<?= time(); ?>" nonce="<?= $nonce; ?>"></script>

</body>
</html>
