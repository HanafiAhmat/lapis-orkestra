<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$csrf_token ??= '';
$password_reset_url ??= '';
?>

<?php $this->layout('layouts:admin.guest', [
    'title' => 'Admin Login',
]) ?>

<?php $redied_user_types ??= []; ?>
<?php if (! in_array('staff', $redied_user_types, true)): ?>
  <div class="alert alert-warning">Staff user type is not ready. Please run migrations.</div>
<?php endif; ?>

<h2 class="text-center mb-4">Admin Login</h2>

<?php if (! empty($fail)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars((string) $fail) ?></div>
<?php endif; ?>

<form method="post">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
      (string) $csrf_token
  ) ?>">
  <input type="hidden" name="type" value="staff">

  <div class="mb-3">
    <label for="email" class="form-label">Email address</label>
    <input
      type="email"
      class="form-control <?= ! empty($errors['email']) ? 'is-invalid' : '' ?>"
      id="email"
      name="email"
      pattern=".+@.+\..+"
      value="<?= htmlspecialchars($old['email'] ?? '') ?>"
      required
    >
    <?php if (! empty($errors['email'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['email']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
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

  <div class="d-grid">
    <button type="submit" class="btn btn-primary">Log In</button>
  </div>

  <div class="row mt-3">
    <div class="col text-end">
      <a href="<?= $password_reset_url; ?>">Reset Password</a>
    </div>
  </div>
</form>
