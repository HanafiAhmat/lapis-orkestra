<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 * 
 * Expected view vars (but may be missing depending on controller):
 * @var string|null $title
 * @var string|null $current_url
 * @var string|null $csrf_token
 * @var array<int, array<string, mixed>|object>|null $records
 * @var array<string, mixed>|null $pagination
 */

$title ??= 'List of records';
$currentUrl = $current_url ?? Lapis::requestUtility()->getCurrentUrl();
$csrfToken = $csrf_token ?? Lapis::sessionUtility()->getCsrfToken();
$records ??= [];
$pagination ??= null;

// Normalize records: object -> array
$normalizedRecords = [];
foreach ($records as $row) {
    if (is_object($row)) {
        $row = method_exists($row, 'toArray') ? $row->toArray() : get_object_vars($row);
    }
    if (is_array($row)) {
        $normalizedRecords[] = $row;
    }
}
$records = $normalizedRecords;

// Headers: derive from first row safely
$headers = [];
if (! empty($records)) {
    $headers = array_keys($records[0]);
}
?>

<?php
  $this->layout('layouts:admin.default', [
    'title' => $title,
    'createAction' => rtrim($currentUrl, '/') . '/create',
  ]);
?>

<?php if (! empty($records)): ?>

  <?php if (is_array($pagination)): ?>
    <?php $this->insert('partials:admin.pagination', ['pagination' => $pagination]); ?>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table-lapis-sm table align-middle">
      <thead>
        <tr>
          <?php foreach ($headers as $header): ?>
            <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $header)), ENT_QUOTES, 'UTF-8') ?></th>
          <?php endforeach; ?>
          <th class="lapis-row-action">Actions</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($records as $row): ?>
          <?php
            $entityId = $row['entity_id'] ?? null;
            $entityIdText = is_scalar($entityId) ? (string) $entityId : '';
          ?>
          <tr>
            <?php foreach ($headers as $key): ?>
              <?php $value = $row[$key] ?? null; ?>
              <td>
                <?php if (is_null($value)): ?>
                  <span class="text-muted">—</span>
                <?php elseif (is_bool($value)): ?>
                  <?= $value ? 'Yes' : 'No' ?>
                <?php elseif (is_scalar($value)): ?>
                  <?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>
                <?php else: ?>
                  <?= htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[complex]', ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
              </td>
            <?php endforeach; ?>

            <td class="lapis-row-action">
              <?php if ($entityIdText !== ''): ?>
                <a href="<?= htmlspecialchars(rtrim($currentUrl, '/') . '/' . $entityIdText, ENT_QUOTES, 'UTF-8') ?>"
                   class="btn btn-sm btn-outline-success mb-1">
                  View
                </a>

                <a href="<?= htmlspecialchars(rtrim($currentUrl, '/') . '/' . $entityIdText . '/edit', ENT_QUOTES, 'UTF-8') ?>"
                   class="btn btn-sm btn-outline-primary mb-1">
                  Edit
                </a>

                <form method="post"
                      action="<?= htmlspecialchars(rtrim($currentUrl, '/') . '/' . $entityIdText, ENT_QUOTES, 'UTF-8') ?>"
                      class="d-inline">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="_method" value="delete">
                  <input type="hidden" name="entity_id" value="<?= htmlspecialchars($entityIdText, ENT_QUOTES, 'UTF-8') ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger mb-1">Delete</button>
                </form>
              <?php else: ?>
                <span class="text-muted">No actions</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>

    </table>
  </div>

  <?php if (is_array($pagination)): ?>
    <?php $this->insert('partials:admin.pagination', ['pagination' => $pagination]); ?>
  <?php endif; ?>

<?php else: ?>

  <div class="alert alert-warning" role="alert">
    <span>No records available.</span>
  </div>

<?php endif; ?>
