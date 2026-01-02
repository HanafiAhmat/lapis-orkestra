<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/** @var array<string, mixed> $pagination */
$pagination = isset($pagination) && is_array($pagination) ? $pagination : [];

/** @return int<1, max> */
$toPositiveInt = static function (mixed $v, int $fallback = 1): int {
    $n = is_int($v) ? $v : (is_numeric($v) ? (int) $v : $fallback);
    return $n >= 1 ? $n : $fallback;
};

/** @return int<0, max> */
$toNonNegativeInt = static function (mixed $v, int $fallback = 0): int {
    $n = is_int($v) ? $v : (is_numeric($v) ? (int) $v : $fallback);
    return $n >= 0 ? $n : $fallback;
};

$totalRecords = $toNonNegativeInt($pagination['total'] ?? 0, 0);
$totalPages   = $toPositiveInt($pagination['total_pages'] ?? 1, 1);

// ---- Fix "Cannot access offset 'num' on mixed" ----
$pageData = (isset($pagination['page']) && is_array($pagination['page'])) ? $pagination['page'] : [];
$currentPage  = $toPositiveInt($pageData['num'] ?? 1, 1);
$perPage      = $toPositiveInt($pageData['limit'] ?? 10, 10);

$currentUrl = is_string($pagination['url_path'] ?? null) ? (string) $pagination['url_path'] : '/';

/** @return array<string, scalar|null|array<string, scalar|null|array<string, scalar|null>>> */
$normalizeQueryValue = static function (mixed $v) use (&$normalizeQueryValue) {
    if ($v === null || is_scalar($v)) {
        return $v;
    }
    if (is_array($v)) {
        $out = [];
        foreach ($v as $k => $vv) {
            $out[$k] = $normalizeQueryValue($vv);
        }
        return $out;
    }
    // Objects/resources: drop to null so http_build_query stays safe
    return null;
};

$currentQueryRaw = (isset($pagination['current_query']) && is_array($pagination['current_query']))
    ? $pagination['current_query']
    : [];

/** @var array<string, scalar|null|array<string, scalar|null|array<string, scalar|null>>> $currentQuery */
$currentQuery = [];
foreach ($currentQueryRaw as $k => $v) {
    if (is_string($k)) {
        $currentQuery[$k] = $normalizeQueryValue($v);
    }
}

// Page size options
$pageSizes = [10, 20, 50, 100];
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 my-3">

  <div class="text-muted small">
    Showing <strong><?= htmlspecialchars((string) $perPage, ENT_QUOTES, 'UTF-8') ?></strong> records per page •
    Page <strong><?= htmlspecialchars((string) $currentPage, ENT_QUOTES, 'UTF-8') ?></strong> of <strong><?= htmlspecialchars((string) $totalPages, ENT_QUOTES, 'UTF-8') ?></strong> •
    Total Records: <strong><?= htmlspecialchars((string) $totalRecords, ENT_QUOTES, 'UTF-8') ?></strong>
  </div>

  <form method="get" class="d-flex align-items-center gap-2">
    <?php foreach ($currentQuery as $key => $value): ?>
      <?php if ($key === 'page') continue; ?>
      <?php
        $v = is_array($value) ? json_encode($value) : (is_scalar($value) ? (string) $value : '');
      ?>
      <input type="hidden"
             name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
             value="<?= htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>

    <label for="pageLimit" class="form-label mb-0 small text-nowrap">Show</label>
    <select name="page[limit]" id="pageLimit" class="form-select form-select-sm" onchange="this.form.submit()">
      <?php foreach ($pageSizes as $size): ?>
        <option value="<?= $size ?>" <?= $size === $perPage ? 'selected' : '' ?>>
          <?= $size ?>
        </option>
      <?php endforeach; ?>
    </select>
  </form>

  <?php if ($totalPages > 1): ?>
    <nav aria-label="Entity pagination">
      <ul class="pagination pagination-sm mb-0">

        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $currentPage > 1
              ? Lapis::viewUtility()->paginationUrl($currentUrl, $currentQuery, $currentPage - 1)
              : '#'
          ?>">&laquo;</a>
        </li>

        <?php
          $maxLinks = 5;
          $half = intdiv($maxLinks, 2);
          $start = max(1, $currentPage - $half);
          $end = min($totalPages, $start + $maxLinks - 1);

          if (($end - $start + 1) < $maxLinks) {
              $start = max(1, $end - $maxLinks + 1);
          }

          $start = (int) $start;
          $end   = (int) $end;
        ?>

        <?php for ($page = $start; $page <= $end; $page++): ?>
          <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>">
            <a class="page-link" href="<?= Lapis::viewUtility()->paginationUrl($currentUrl, $currentQuery, $page) ?>">
              <?= htmlspecialchars((string) $page, ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
        <?php endfor; ?>

        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link" href="<?= $currentPage < $totalPages
              ? Lapis::viewUtility()->paginationUrl($currentUrl, $currentQuery, $currentPage + 1)
              : '#'
          ?>">&raquo;</a>
        </li>

      </ul>
    </nav>
  <?php endif; ?>

</div>
