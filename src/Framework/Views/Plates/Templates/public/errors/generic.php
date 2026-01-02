<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/**
 * Normalize error payload to predictable scalar strings for templates.
 *
 * Expected input: $fe = [
 *   'status_code' => int|string,
 *   'status_text' => string,
 *   'class'       => string,
 *   'message'     => string,
 *   'file'        => string,
 *   'line'        => int|string,
 * ]
 */

/** @var array<string, mixed> $fe */
$fe = (isset($fe) && is_array($fe)) ? $fe : [];

/** @var string $statusCode */
$statusCode = is_scalar($fe['status_code'] ?? null) ? (string) $fe['status_code'] : '500';
$statusCode = $statusCode !== '' ? $statusCode : '500';

/** @var string $statusText */
$statusText = is_scalar($fe['status_text'] ?? null) ? (string) $fe['status_text'] : 'Server Error';

/** @var string $exceptionClass */
$exceptionClass = is_scalar($fe['class'] ?? null) ? (string) $fe['class'] : '';

/** @var string $exceptionMessage */
$exceptionMessage = is_scalar($fe['message'] ?? null) ? (string) $fe['message'] : '';

/** @var string $exceptionFile */
$exceptionFile = is_scalar($fe['file'] ?? null) ? (string) $fe['file'] : '';

/** @var string $exceptionLine */
$exceptionLine = is_scalar($fe['line'] ?? null) ? (string) $fe['line'] : '';

/**
 * APP_DEBUG usually comes from env and ends up as "1", "true", "0", "false".
 * Don't compare to boolean true directly.
 */

/** @var string $debugRaw */
$debugRaw = $_SERVER['APP_DEBUG'] ?? '';
$debug = in_array(strtolower($debugRaw), ['1', 'true', 'yes', 'on'], true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error <?= htmlspecialchars($statusCode, ENT_QUOTES, 'UTF-8'); ?></title>
  <style>
      body { font-family: Arial, sans-serif; background-color: #f2f2f2; color: #333; text-align: center; padding: 3rem; }
      h1 { font-size: 2em; margin-bottom: 0.5em; }
      p { margin: 1em 0; }
      .code { font-weight: bold; }
      .details { margin-top: 2em; font-size: 0.9em; color: #666; }
  </style>
</head>
<body>
  <h1>Oops! Something went wrong.</h1>
  <p>We're sorry, but an unexpected error occurred.</p>

  <p class="code">Error Code: <?= htmlspecialchars($statusCode, ENT_QUOTES, 'UTF-8'); ?></p>
  <p class="code">Error Message: <?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8'); ?></p>

  <?php if ($debug): ?>
    <div class="details">
      <p>
        <strong>Exception:</strong>
        <?= htmlspecialchars($exceptionClass, ENT_QUOTES, 'UTF-8'); ?>
        <?php if ($exceptionMessage !== ''): ?>
          &nbsp;=>&nbsp;<?= htmlspecialchars($exceptionMessage, ENT_QUOTES, 'UTF-8'); ?>
        <?php endif; ?>
      </p>

      <?php if ($exceptionFile !== '' || $exceptionLine !== ''): ?>
        <pre><?= htmlspecialchars($exceptionFile . ($exceptionLine !== '' ? ' : ' . $exceptionLine : ''), ENT_QUOTES, 'UTF-8'); ?></pre>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</body>
</html>
