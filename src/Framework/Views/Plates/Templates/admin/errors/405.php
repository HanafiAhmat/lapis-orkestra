<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;
use Stringable;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/** @return string */
$toString = static function (mixed $v, string $fallback = ''): string {
    if (is_string($v)) {
        return $v;
    }
    if (is_int($v) || is_float($v) || is_bool($v)) {
        return (string) $v;
    }
    if ($v instanceof Stringable) {
        return (string) $v;
    }
    return $fallback;
};

$dashboardUrl = $toString(Lapis::configRegistry()->get('app.routes.admin_prefix'), '/admin');
?>

<?php $this->layout('layouts:admin.default', [
    'title' => '405 - Method not allowed!',
]); ?>

<div class="container py-5 text-center">
  <div class="mb-4">
    <i class="bi bi-exclamation-octagon" style="font-size: 4rem;"></i>
  </div>

  <h1 class="display-5 fw-bold text-danger mb-3">405 - Method not allowed!</h1>
  <p class="lead text-muted">
    The page you’re trying to access doesn't allow the used request method.
  </p>

  <div class="mt-4">
    <a href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">
      <i class="bi bi-speedometer2 me-1"></i> Back to Dashboard
    </a>
    <a href="javascript:history.back();" class="btn btn-outline-secondary ms-2">
      <i class="bi bi-arrow-left-circle me-1"></i> Go Back
    </a>
  </div>
</div>
