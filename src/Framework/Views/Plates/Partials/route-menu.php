<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Framework\DTO\RouteDefinition;

/** @return bool */
$isStaffUser = static function (mixed $u): bool {
    if (!is_object($u)) {
        return false;
    }
    if (property_exists($u, 'user_type')) {
        $t = $u->user_type; // mixed
        return is_string($t) && $t === 'staff';
    }
    if (method_exists($u, 'getUserType')) {
        $t = $u->getUserType(); // mixed
        return is_string($t) && $t === 'staff';
    }
    return false;
};

$userRaw = Lapis::varRegistry()->getOrSkip('user');
$isStaff = $isStaffUser($userRaw);

// Always define arrays for PHPStan
/** @var array<int, RouteDefinition> $visiblePublicRoutes */
$visiblePublicRoutes = [];
/** @var array<int, RouteDefinition> $visibleAdminRoutes */
$visibleAdminRoutes = [];

// ---- Public routes (GET only) ----
$publicRoutes = Lapis::routeRegistry()->allPublicRoutes();

foreach ($publicRoutes as $route) { // RouteCollection is iterable
    if ($route->method !== 'GET') {
        continue;
    }
    $visiblePublicRoutes[] = $route;
}

usort(
    $visiblePublicRoutes,
    static fn (RouteDefinition $a, RouteDefinition $b): int => strcmp($a->path, $b->path)
);

// ---- Admin routes (GET only), only if staff ----
if ($isStaff) {
    $adminRoutes = Lapis::routeRegistry()->allAdminRoutes();

    foreach ($adminRoutes as $route) {
        if ($route->method !== 'GET') {
            continue;
        }
        $visibleAdminRoutes[] = $route;
    }

    usort(
        $visibleAdminRoutes,
        static fn (RouteDefinition $a, RouteDefinition $b): int => strcmp($a->path, $b->path)
    );
}
?>

<nav class="route-sidebar" id="routeMenu">
  <h2 class="sidebar-title"><u>Routes</u></h2>

  <h4 class="sidebar-title">Public</h4>
  <ul class="route-list">
    <?php foreach ($visiblePublicRoutes as $route): ?>
      <li>
        <a href="<?= htmlspecialchars($route->path, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($route->method . ' ' . $route->path, ENT_QUOTES, 'UTF-8') ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <?php if ($isStaff): ?>
    <h4 class="sidebar-title">Admin</h4>
    <ul class="route-list">
      <?php foreach ($visibleAdminRoutes as $route): ?>
        <li>
          <a href="<?= htmlspecialchars($route->path, ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($route->method . ' ' . $route->path, ENT_QUOTES, 'UTF-8') ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</nav>

<button class="route-toggle" id="routeToggle">☰ Routes</button>
