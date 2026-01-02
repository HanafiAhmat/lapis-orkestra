<?php declare(strict_types=1); ?>

<div class="text-center">
  <h4 class="fw-bold mb-1"><?= $title ?? '' ?></h4>
  <p class="text-muted mb-2"><?= $message ?? '' ?></p>
  <div class="alert alert-warning">
    <?php if (! empty($commands)): ?>
      <ol>
        <?php foreach ($commands as $command): ?>
          <li><?= $command; ?></li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>
</div>
