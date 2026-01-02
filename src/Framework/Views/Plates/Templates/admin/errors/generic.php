<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 * @var array<string, mixed>|null $fe
 */

$feInput = (isset($fe) && is_array($fe)) ? $fe : null;

$defaults = [
    'status_code' => '500',
    'status_text' => 'Server Error',
    'class' => '',
    'message' => '',
    'file' => '',
    'line' => '',
];

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

// Normalize into a string-only array so PHPStan stops complaining.
/** @var array{status_code:string,status_text:string,class:string,message:string,file:string,line:string} $feSafe */
$feSafe = [
    'status_code' => $toString($feInput['status_code'] ?? $defaults['status_code'], $defaults['status_code']),
    'status_text' => $toString($feInput['status_text'] ?? $defaults['status_text'], $defaults['status_text']),
    'class'       => $toString($feInput['class'] ?? $defaults['class']),
    'message'     => $toString($feInput['message'] ?? $defaults['message']),
    'file'        => $toString($feInput['file'] ?? $defaults['file']),
    'line'        => $toString($feInput['line'] ?? $defaults['line']),
];

$isDebug = false;
if (isset($_SERVER['APP_DEBUG'])) {
    // common env values: "1", "true", 1, true
    $raw = $_SERVER['APP_DEBUG'];
    $isDebug = ($raw === true || $raw === 1 || $raw === '1' || $raw === 'true');
}

$fileLine = $feSafe['file'] !== '' || $feSafe['line'] !== ''
    ? ($feSafe['file'] . ' : ' . $feSafe['line'])
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= htmlspecialchars($feSafe['status_code'], ENT_QUOTES, 'UTF-8') ?></title>
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

    <p class="code">Error Code: <?= htmlspecialchars($feSafe['status_code'], ENT_QUOTES, 'UTF-8') ?></p>
    <p class="code">Error Message: <?= htmlspecialchars($feSafe['status_text'], ENT_QUOTES, 'UTF-8') ?></p>

    <div class="details">
        <?php if ($isDebug): ?>
            <p>
                <strong>Exception Message:</strong>
                <?= htmlspecialchars($feSafe['class'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($feSafe['message'] !== ''): ?>
                    => <?= htmlspecialchars($feSafe['message'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </p>

            <?php if ($fileLine !== ''): ?>
                <pre><?= htmlspecialchars($fileLine, ENT_QUOTES, 'UTF-8') ?></pre>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
