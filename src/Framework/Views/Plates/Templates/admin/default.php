<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/** @var string $csrfToken */
$csrfToken = Lapis::sessionUtility()->getCsrfToken();

/** @var string $currentUrl */
$currentUrl = Lapis::requestUtility()->getCurrentUrl();

/**
 * Render mixed into safe HTML.
 * - Scalars: inline
 * - null: muted "null"
 * - arrays/objects: collapsible pretty JSON
 */
$renderValue = static function (mixed $value, string $collapseId): string {
    // null
    if ($value === null) {
        return '<span class="text-muted">null</span>';
    }

    // string
    if (is_string($value)) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // int/float
    if (is_int($value) || is_float($value)) {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    // bool
    if (is_bool($value)) {
        return $value
            ? '<span class="badge text-bg-success">true</span>'
            : '<span class="badge text-bg-secondary">false</span>';
    }

    // resource
    if (is_resource($value)) {
        return '<span class="text-muted">&lt;resource&gt;</span>';
    }

    // arrays / objects
    $normalized = $value;
    $typeLabel = is_array($value) ? 'Array' : 'Object';

    if (is_object($value)) {
        $normalized = method_exists($value, 'toArray')
            ? $value->toArray()
            : get_object_vars($value);
    }

    $json = json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        return '<span class="text-danger">[unserializable]</span>';
    }

    $escaped = htmlspecialchars($json, ENT_QUOTES, 'UTF-8');

    // Button + collapse panel (Bootstrap 5)
    return '
      <div class="d-flex flex-column gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge text-bg-info">' . $typeLabel . '</span>
          <button class="btn btn-sm btn-outline-secondary"
                  type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#' . $collapseId . '"
                  aria-expanded="false"
                  aria-controls="' . $collapseId . '">
            View details
          </button>
        </div>

        <div class="collapse" id="' . $collapseId . '">
          <div class="border rounded bg-body-tertiary p-2">
            <pre class="mb-0 small"><code>' . $escaped . '</code></pre>
          </div>
        </div>
      </div>
    ';
};
?>

<?php $this->layout('layouts:admin.default', [
    'title' => 'Lapis Orkestra',
]); ?>

<?php if (isset($records)): ?> 
  <?php if (! empty($records)): ?>
    <div class="table-responsive">
      <table class="styled-table">
        <thead>
          <tr>
            <?php foreach (array_keys((array) $records[0]) as $header): ?>
              <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $header))) ?></th>
            <?php endforeach; ?>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($records as $row): ?>
            <tr>
              <?php
                /** @var string $value @@phpstan */
                foreach ((array) $row as $value):
                    ?>
                <td><?= $value !== null ? htmlspecialchars($value) : '' ?></td>
              <?php endforeach; ?>
              <td>
                <button type="button" onclick="window.location.href='<?= $currentUrl ?>/edit/<?= $row['entity_id'] ?>'">Edit</button>&nbsp;
                <form method="post" action="<?= $currentUrl ?>/<?= $row['entity_id'] ?>">
                  <input type="hidden" name="csrf_token" value="<?= $this->e($csrfToken) ?>">
                  <input type="hidden" name="_method" value="delete">
                  <button type="submit">Delete</button>
                </form>&nbsp;
              </td>
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
      <?php
        $i = 0;
        foreach ((array) $record as $key => $value):
          $i++;
          $collapseId = 'record-field-' . $i . '-' . preg_replace('/[^a-z0-9\-_]/i', '-', (string) $key);
      ?>
        <li><strong><?= $this->e(ucwords(str_replace('_', ' ', (string) $key))) ?>:</strong> <?= $renderValue($value, $collapseId) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php else: ?>
  <?php
    $arr = get_defined_vars();
    dump($arr ?: []);
  ?>
<?php endif; ?>
