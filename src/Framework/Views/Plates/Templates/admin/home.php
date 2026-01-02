<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Framework\DTO\WidgetDefinition;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/** @var array<int, WidgetDefinition> $widgets */
$widgets = Lapis::adminWidgetRegistry()->getBySection('dashboard');

if (! is_array($widgets)) {
    $widgets = [];
}
?>

<?php $this->layout('layouts:admin.default', [
    'title' => 'Dashboard',
]); ?>

<div class="row">
  <?php foreach ($widgets as $widget): ?>
    <?php
      // Defaults
      $colClass = is_string($widget->colClass) && $widget->colClass !== '' ? $widget->colClass : 'col-12';
      $containerClass = is_string($widget->containerClass) && $widget->containerClass !== '' ? $widget->containerClass : 'card';
      $title = is_string($widget->title) ? $widget->title : '';

      // Render: callable|string only; otherwise empty
      $html = '';
      $render = $widget->render;

      if (is_callable($render)) {
          $out = $render();
          $html = is_string($out) ? $out : (string) $out;
      } elseif (is_string($render)) {
          $html = $render;
      }
    ?>

    <div class="<?= htmlspecialchars($colClass, ENT_QUOTES, 'UTF-8') ?> mb-4">
      <div class="<?= htmlspecialchars($containerClass, ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($title !== ''): ?>
          <div class="card-header fw-semibold">
            <span class="card-title mb-0"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
          </div>
        <?php endif; ?>

        <div class="card-body">
          <?= $html ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
