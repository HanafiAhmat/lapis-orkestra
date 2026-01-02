<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/**
 * Expected data (from InstallGuardMiddleware):
 * - $error    string  e.g. "Database is not prepared."
 * - $hint     string  e.g. "Run migrations and seeders."
 * - $commands array   e.g. ["php bin/console db:migrate", "php bin/console db:seed --class=..."]
 * - $details  ?string Only present when app.debug = true (or APP_DEBUG=1)
 */

/** @var string $appName */
$appName = Lapis::configRegistry()->get('app.name') ?: 'Lapis Orkestra';
$isDebug = (bool) (Runtime::isDebug());
$isDev = (bool) (Runtime::isDev());

// CDN Bootstrap (public-safe)
$bootstrapCss = '/assets/default/shared/css/bootstrap-theme.css'; // your compiled theme if available
$logoUrl = '/assets/default/admin/images/lapis-logo.png';

?>

<?php $this->layout('layouts:admin.setup', [
    'title' => 'Setup Required',
]); ?>

<div class="container px-3">
  <div class="card shadow-lg mx-auto">
    <div class="card-body p-4 p-md-5">
      <div class="brand mb-3">
        <img src="<?= htmlspecialchars(
            $logoUrl
        ) ?>" alt="Logo">
        <h1 class="h4 m-0 text-info"><?= htmlspecialchars($appName) ?></h1>
      </div>

      <div class="alert alert-warning d-flex align-items-start" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <div>
          <strong>Setup Required</strong><br>
          <?= htmlspecialchars($error ?? 'The application database is not ready.') ?>
        </div>
      </div>

      <?php if (! empty($hint)): ?>
        <p class="mb-3 text-secondary-emphasis">
          <?= htmlspecialchars((string) $hint) ?>
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
            <?php
              $defaultCommands = ['php bin/console db:migrate', 'php bin/console db:seed'];
          $cmds = isset($commands) && is_array($commands) && $commands ? $commands : $defaultCommands;
          ?>
            <?php foreach ($cmds as $cmd): ?>
              <pre class="mb-2"><code><?= is_string($cmd) ? htmlspecialchars($cmd) : ''; ?></code></pre>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if ($isDebug && ! empty($details)): ?>
          <div class="mb-4">
            <h2 class="h6 text-uppercase text-secondary">Debug details</h2>
            <pre class="mb-0"><code><?= htmlspecialchars((string) $details) ?></code></pre>
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
