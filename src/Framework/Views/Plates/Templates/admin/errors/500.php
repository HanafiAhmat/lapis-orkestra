<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;
use Stringable;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/**
 * Expected inputs (optional):
 * - $fe: array{message?: mixed, file?: mixed, line?: mixed}|mixed
 * - $error: mixed
 * - $details: mixed
 */

// ---------- local helpers ----------
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

/** @return list<string> */
$toStringList = static function (mixed $v): array {
    if (! is_array($v)) {
        return [];
    }
    $out = [];
    foreach ($v as $item) {
        $s = is_string($item) ? $item : (is_scalar($item) ? (string) $item : '');
        if ($s !== '') {
            $out[] = $s;
        }
    }
    return $out;
};

// ---------- app config (avoid offset on mixed) ----------
$appConfigRaw = Lapis::configRegistry()->get('app');
$appConfig = is_array($appConfigRaw) ? $appConfigRaw : [];

$debugMode = (bool) ($appConfig['debug'] ?? false);
$appEnv = $toString($appConfig['env'] ?? null, 'production');
$adminPrefix = $toString(Lapis::configRegistry()->get('app.routes.admin_prefix'), '/admin');

// ---------- normalize error payload ----------
$errorMessage = 'Unknown server error';
/** @var list<string> $errorDetails */
$errorDetails = [];

if (isset($fe) && is_array($fe)) {
    // message
    $errorMessage = $toString($fe['message'] ?? null, $errorMessage);

    // file:line
    $file = $toString($fe['file'] ?? null, '');
    $line = $toString($fe['line'] ?? null, '');

    if ($file !== '' || $line !== '') {
        $errorDetails[] = trim($file . ($line !== '' ? ' : ' . $line : ''));
    }
} else {
    // fallback to $error + $details
    $errorMessage = $toString($error ?? null, $errorMessage);

    // $details may be string or array
    $detailsText = $toString($details ?? null, '');
    if ($detailsText !== '') {
        $errorDetails[] = $detailsText;
    } else {
        $errorDetails = $toStringList($details ?? null);
    }
}
?>

<?php $this->layout('layouts:admin.default', [
  'title' => 'Server Error',
]); ?>

<div class="container py-5 text-center">
  <div class="mb-4">
    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
  </div>

  <h1 class="display-5 fw-bold text-danger mb-3">Whoops! Something went wrong.</h1>
  <p class="lead text-muted">The server encountered an internal error and could not complete your request.</p>

  <?php if ($debugMode || $appEnv === 'development'): ?>
    <div class="alert alert-warning text-start mx-auto my-4" style="max-width: 700px;">
      <h5 class="alert-heading"><i class="bi bi-bug-fill me-1"></i> Debug Information</h5>
      <hr>
      <p class="mb-2"><strong>Error:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>

      <?php if ($errorDetails !== []): ?>
        <ul class="small mb-0">
          <?php foreach ($errorDetails as $detail): ?>
            <li><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="mt-4">
    <a href="<?= htmlspecialchars($adminPrefix, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">
      <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
    </a>
    <a href="javascript:location.reload();" class="btn btn-outline-secondary ms-2">
      <i class="bi bi-arrow-clockwise me-1"></i> Reload Page
    </a>
  </div>
</div>
