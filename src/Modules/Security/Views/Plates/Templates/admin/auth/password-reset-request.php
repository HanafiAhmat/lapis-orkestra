<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$csrf_token ??= [];
$login_url ??= [];
?>

<?php $this->layout('layouts:admin.guest', [
    'title' => 'Password Reset Request Form',
]); ?>

<?php $redied_user_types ??= []; ?>
<?php if (! in_array('staff', $redied_user_types, true)): ?>
  <div class="alert alert-warning">Staff user type is not ready. Please run migrations.</div>
<?php endif; ?>

<?php if (! empty($fail)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars((string) $fail) ?></div>
<?php endif; ?>

<?php if (! empty($success)): ?>
  <div class="alert alert-info"><?= htmlspecialchars((string) $success) ?></div>
<?php endif; ?>

<h2 class="text-center mb-4">Password Reset Request</h2>

<?php if (! empty($fail)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars((string) $fail) ?></div>
<?php endif; ?>

<form method="post">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
      (string) $csrf_token
  ) ?>">
  <input type="hidden" name="type" value="staff">

  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input
      type="email"
      class="form-control <?= ! empty($errors['email']) ? 'is-invalid' : '' ?>"
      id="email"
      name="email"
      pattern=".+@.+\..+"
      required
      value="<?= htmlspecialchars($old['email'] ?? '') ?>"
    >
    <?php if (! empty($errors['email'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['email']) ?></div>
    <?php endif; ?>
  </div>

  <div class="d-grid">
    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
      Send Request
    </button>
  </div>

  <div class="row mt-3">
    <div class="col text-end">
      <a href="<?= $login_url; ?>">Login</a>
    </div>
  </div>
</form>
