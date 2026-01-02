<?php declare(strict_types=1);

$stats ??= [
    'uptime' => 'Unknown',
    'loadAvg' => 'Unknown',
    'phpVersion' => PHP_VERSION,
    'memoryUsage' => [
        'used' => 'Unknown',
        'total' => 'Unknown',
    ],
];
?>

<ul class="list-unstyled mb-0 small">
  <li><strong>PHP Version:</strong> <?= htmlspecialchars(
      (string) $stats['phpVersion']
  ) ?></li>
  <li><strong>System Uptime:</strong> <?= htmlspecialchars((string) $stats['uptime']) ?></li>
  <li><strong>Load Average:</strong> <?= htmlspecialchars((string) $stats['loadAvg']) ?></li>
  <?php if ($stats['memoryUsage']): ?>
    <li><strong>Memory:</strong> <?= htmlspecialchars(
        (string) $stats['memoryUsage']['used']
    ) ?> / <?= htmlspecialchars((string) $stats['memoryUsage']['total']) ?></li>
  <?php endif; ?>
</ul>
