<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$groupByPsr ??= [];
?>

<?php $this->layout('layouts:admin.default', [
    'title' => 'PSR Attribute Scan Summary',
]); ?>

<?php foreach ($groupByPsr as $psr => $interfaces): ?>
  <h2><?= $psr ?></h2>

  <?php foreach ($interfaces as $interface => $psrAttributes): ?>
    <?= $psr ?>: <a href="<?= $psrAttributes[0]->link ?>"><?= $interface ?></a>
    <div class="table-responsive">
      <table class="styled-table">
        <thead>
          <tr>
            <th>Classname</th>
            <th>Filename</th>
            <th>Usage</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($psrAttributes as $psrAttribute): ?>
            <tr>
              <td><?= $psrAttribute->class ?></td>
              <td><?= $psrAttribute->file ?></td>
              <td><?= $psrAttribute->usage ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>

  <hr>
<?php endforeach; ?>
