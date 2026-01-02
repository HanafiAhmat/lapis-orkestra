<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use Stringable;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/**
 * Expected data (from boot/setup guard):
 * - $error    string
 * - $hint     string
 * - $commands array<int, string>
 * - $details  ?string (only when debug)
 */

// --- Safe helpers (local, template-only) ---
/** @return non-empty-string */
$toNonEmptyString = static function (mixed $value, string $fallback): string {
    if (is_string($value) && $value !== '') {
        return $value;
    }
    if (is_scalar($value)) {
        $s = (string) $value;
        return $s !== '' ? $s : $fallback;
    }
    if ($value instanceof Stringable) {
        $s = (string) $value;
        return $s !== '' ? $s : $fallback;
    }
    return $fallback;
};

/** @return string */
$toString = static function (mixed $value, string $fallback = ''): string {
    if (is_string($value)) {
        return $value;
    }
    if (is_scalar($value)) {
        return (string) $value;
    }
    if ($value instanceof Stringable) {
        return (string) $value;
    }
    return $fallback;
};

$appNameRaw = Lapis::configRegistry()->get('app.name');
$appName = $toNonEmptyString($appNameRaw, 'Lapis Orkestra');

$isDebug = Runtime::isDebug();
$isDev   = Runtime::isDev();

$logoUrl = '/assets/default/admin/images/lapis-logo.png';

// Normalize inputs from controller/boot (avoid mixed later)
$errorText   = $toString($error ?? null, 'The application database is not ready.');
$hintText    = $toString($hint ?? null, '');
$detailsText = $toString($details ?? null, '');

// Build dynamically to avoid fixed-shape inference in PHPStan
$defaultCommands = [];
$defaultCommands[] = 'php bin/console db:migrate';
$defaultCommands[] = 'php bin/console db:seed';

$cmds = $defaultCommands;
if (isset($commands) && is_array($commands) && $commands !== []) {
    $tmp = [];
    foreach ($commands as $c) {
        $s = $toString($c, '');
        if ($s !== '') {
            $tmp[] = $s;
        }
    }
    if ($tmp !== []) {
        $cmds = $tmp;
    }
}
?>

<?php $this->layout('layouts:admin.setup', [
    'title' => 'Setup Required',
]); ?>

<div class="container px-3">
  <div class="card shadow-lg mx-auto">
    <div class="card-body p-4 p-md-5">
      <div class="brand mb-3">
        <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
        <h1 class="h4 m-0 text-info"><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></h1>
      </div>

      <div class="alert alert-warning d-flex align-items-start" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>
          <strong>Setup Required</strong><br>
          <?= htmlspecialchars($errorText, ENT_QUOTES, 'UTF-8') ?>
        </div>
      </div>

      <?php if ($hintText !== ''): ?>
        <p class="mb-3 text-secondary-emphasis">
          <?= htmlspecialchars($hintText, ENT_QUOTES, 'UTF-8') ?>
        </p>
      <?php endif; ?>

      <?php if ($isDev): ?>
        <div class="mb-4">
          <h2 class="h6 text-uppercase text-secondary">What to do</h2>
          <ol class="mb-0">
            <li>Ensure the database connection is configured correctly in <code>.env</code> / config.</li>
            <li>Run the migrations<?php if ($isDebug): ?> and (optionally) seeders<?php endif; ?> from the project root:</li>
          </ol>

          <div class="mt-3">
            <?php foreach ($cmds as $cmd): ?>
              <pre class="mb-2"><code><?= htmlspecialchars($cmd, ENT_QUOTES, 'UTF-8') ?></code></pre>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if ($isDebug && $detailsText !== ''): ?>
          <div class="mb-4">
            <h2 class="h6 text-uppercase text-secondary">Debug details</h2>
            <pre class="mb-0"><code><?= htmlspecialchars($detailsText, ENT_QUOTES, 'UTF-8') ?></code></pre>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
          <a href="/" class="btn btn-outline-light">
            <i class="bi bi-house-door me-1"></i> Go to Homepage
          </a>
          <button type="button" class="btn btn-info" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i> Retry
          </button>
        </div>

        <p class="mt-4 mb-0 small text-secondary text-opacity-75">
          If this is a production environment, database preparation is intentionally disabled on boot.
          Please run the commands on your server/CI, then refresh this page.
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>
