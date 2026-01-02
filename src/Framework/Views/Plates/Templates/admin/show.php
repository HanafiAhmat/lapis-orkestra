<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 * @var array<string, mixed>|object|null $record
 * @var string|null $title
 * @var string|null $back_action 
 **/

$record ??= [];
$title ??= 'Record details';
$backAction = $back_action ?? '/admin';

if (is_object($record)) {
    $record = method_exists($record, 'toArray')
        ? $record->toArray()
        : get_object_vars($record);
}

if (! is_array($record)) {
    $record = [];
}

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
    'title' => $title,
    'backAction' => $backAction,
]); ?>

<div class="table-responsive">
  <table class="table-lapis-sm table">
    <?php if (empty($record)): ?>
      <tr>
        <td colspan="2"><em>No data available.</em></td>
      </tr>
    <?php else: ?>
      <?php
        $i = 0;
        foreach ($record as $key => $value):
          $i++;
          $collapseId = 'record-field-' . $i . '-' . preg_replace('/[^a-z0-9\-_]/i', '-', (string) $key);
      ?>
        <tr>
          <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $key)), ENT_QUOTES, 'UTF-8') ?></th>
          <td><?= $renderValue($value, $collapseId) ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </table>
</div>
