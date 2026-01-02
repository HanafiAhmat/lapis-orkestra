<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$token ??= '';
$csrf_token ??= '';
$masked_email ??= '';
$login_url ??= '';
?>

<?php $this->layout('layouts:admin.guest', [
    'title' => 'Password Reset Confirmation',
]); ?>

<?php $redied_user_types ??= []; ?>
<?php if (! in_array('staff', $redied_user_types, true)): ?>
  <div class="alert alert-warning">Staff user type is not ready. Please run migrations.</div>
<?php endif; ?>

<h2>Password Reset Confirmation</h2>

<?php if (! empty($success)): ?>
  <div class="alert alert-info"><?= htmlspecialchars((string) $success) ?></div>
<?php else: ?>
  <?php if (! empty($fail)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars((string) $fail) ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
        (string) $csrf_token
    ) ?>">
    <input type="hidden" name="type" value="customer">
    <input type="hidden" name="token" value="<?= htmlspecialchars((string) $token) ?>">

    <div class="mb-4">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email" value="<?= $masked_email; ?>" disabled>
    </div>

    <div class="mb-4">
      <label for="password" class="form-label">Password</label>
      <input
        type="password"
        class="form-control <?= ! empty($errors['password']) ? 'is-invalid' : '' ?>"
        id="password"
        name="password"
        required
      >
      <?php if (! empty($errors['password'])): ?>
        <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['password']) ?></div>
      <?php endif; ?>
    </div>

    <div class="mb-4">
      <label for="password_confirm" class="form-label">Password Confirmation</label>
      <input
        type="password"
        class="form-control <?= ! empty($errors['password_confirm']) ? 'is-invalid' : '' ?>"
        id="password_confirm"
        name="password_confirm"
        required
      >
      <?php if (! empty($errors['password_confirm'])): ?>
        <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['password_confirm']) ?></div>
      <?php endif; ?>
    </div>

    <div class="d-grid">
      <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
        Set New Password
      </button>
    </div>
  </form>
<?php endif; ?>

<div class="row mt-3">
  <div class="col text-end">
    <a href="<?= $login_url; ?>">Login</a>
  </div>
</div>
