<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$csrf_token = Lapis::sessionUtility()->getCsrfToken();
$current_url = Lapis::requestUtility()->getCurrentUrl();
?>

<?php $this->layout('layouts:public.default', [
    'title' => $title ?? 'Lapis Orkestra',
]); ?>

<?php if (isset($records)): ?> 
  <?php if (! empty($records)): ?>
    <div class="table-responsive">
      <table class="table-lapis-sm table align-middle">
        <thead>
          <tr>
            <?php foreach (array_keys((array) $records[0]) as $header): ?>
              <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $header))) ?></th>
            <?php endforeach; ?>
            <?php if (isset($records[0]['entity_id'])): ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($records as $row): ?>
            <tr>
              <?php
                /** @var string $value @@phpstan */
                foreach ((array) $row as $value):
                    ?>
                <td>
                  <?php if (! empty($value)): ?>
                    <?php if (is_array($value)): ?> 
                      <?= implode(', ', array_map(htmlspecialchars(...), $value)) ?>
                    <?php else: ?> 
                      <?= htmlspecialchars($value) ?>
                    <?php endif; ?> 
                  <?php endif; ?> 
                </td>
              <?php endforeach; ?>
              <?php if (isset($row['entity_id'])): ?>
                <td>
                  <button type="button" onclick="window.location.href='<?= $current_url ?>/edit/<?= $row['entity_id'] ?>'">Edit</button>&nbsp;
                  <form method="post" action="<?= $current_url ?>/<?= $row['entity_id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $this->e($csrf_token) ?>">
                    <input type="hidden" name="_method" value="delete">
                    <button type="submit">Delete</button>
                  </form>&nbsp;
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php if (! empty($pagination)): ?>
      <div class="pagination">
        <?php if (! empty($pagination['prev_url'])): ?>
          <a href="<?= htmlspecialchars(
              (string) $pagination['prev_url']
          ) ?>" class="pagination-button">&laquo; Prev</a>
        <?php endif; ?>

        <?php if (! empty($pagination['next_url'])): ?>
          <a href="<?= htmlspecialchars(
              (string) $pagination['next_url']
          ) ?>" class="pagination-button">Next &raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="no-records">
      <p>No records available.</p>
    </div>
  <?php endif; ?>
<?php elseif (isset($record)): ?>
  <div class="record-details">
    <ul>
      <?php foreach ((array) $record as $key => $value): ?>
        <li><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</strong> <?= htmlspecialchars(
            (string) $value
        ) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php else: ?>
  <?php
            $arr = get_defined_vars();
    dump($arr ?: []);
    ?>
<?php endif; ?>
