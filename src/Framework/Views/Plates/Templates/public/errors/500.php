<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Runtime;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$isDebug = Runtime::isDebug();
$isDev   = Runtime::isDev();

/** @var array<string, mixed> $fe */
$fe = (isset($fe) && is_array($fe)) ? $fe : [];

// Normalize to safe strings (no mixed echoes)
$statusCode = is_scalar($fe['status_code'] ?? null) ? (string) $fe['status_code'] : '500';
$statusCode = $statusCode !== '' ? $statusCode : '500';

$statusText = is_scalar($fe['status_text'] ?? null) ? (string) $fe['status_text'] : 'Server Error';
$exClass    = is_scalar($fe['class'] ?? null) ? (string) $fe['class'] : '';
$exMessage  = is_scalar($fe['message'] ?? null) ? (string) $fe['message'] : '';
$exFile     = is_scalar($fe['file'] ?? null) ? (string) $fe['file'] : '';
$exLine     = is_scalar($fe['line'] ?? null) ? (string) $fe['line'] : '';

?>

<?php $this->layout('layouts:public.default', [
    'title' => 'Whoops, Something went wrong on the server!',
]); ?>

<div class="error-container">
  <div class="error-icon">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z"/>
    </svg>
  </div>

  <h1>Whoops, Something went wrong on the server.</h1>
  <p>We're sorry, but an unexpected error occurred.</p>

  <?php if ($isDev || $isDebug): ?>
    <p class="code">Error Code: <?= htmlspecialchars($statusCode, ENT_QUOTES, 'UTF-8'); ?></p>
    <p class="code">Error Message: <?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8'); ?></p>

    <div class="details">
      <p>
        <strong>Exception Message:</strong>
        <?= htmlspecialchars($exClass, ENT_QUOTES, 'UTF-8'); ?>
        <?php if ($exMessage !== ''): ?>
          &nbsp;=>&nbsp;<?= htmlspecialchars($exMessage, ENT_QUOTES, 'UTF-8'); ?>
        <?php endif; ?>
      </p>

      <?php if ($exFile !== '' || $exLine !== ''): ?>
        <pre><?= htmlspecialchars($exFile . ($exLine !== '' ? ' : ' . $exLine : ''), ENT_QUOTES, 'UTF-8'); ?></pre>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
