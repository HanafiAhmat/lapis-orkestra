<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$post_action ??= '';
$csrf_token ??= '';
?>

<?php $this->layout('layouts:admin.default', [
    'title' => 'Create new staff record',
    'backAction' => $back_action ?? ''
]) ?>

<form method="post" action="<?= $post_action; ?>" class="col-md-5">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
      (string) $csrf_token
  ) ?>">

  <div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input
      type="text"
      class="form-control <?= ! empty($errors['name']) ? 'is-invalid' : '' ?>"
      id="name"
      name="name"
      value="<?= htmlspecialchars($old['name'] ?? '') ?>"
      minlength="5"
      maxlength="150"
      required
    >
    <?php if (! empty($errors['name'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['name']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="email" class="form-label">Email address</label>
    <input
      type="email"
      class="form-control <?= ! empty($errors['email']) ? 'is-invalid' : '' ?>"
      id="email"
      name="email"
      pattern=".+@.+\..+"
      value="<?= htmlspecialchars($old['email'] ?? '') ?>"
      minlength="5"
      maxlength="150"
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
      minlength="8"
      maxlength="64"
      required
    >
    <?php if (! empty($errors['password'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['password']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="password_confirm" class="form-label">Password Confirmation</label>
    <input
      type="password"
      class="form-control <?= ! empty($errors['password_confirm']) ? 'is-invalid' : '' ?>"
      id="password_confirm"
      name="password_confirm"
      minlength="8"
      maxlength="64"
      required
    >
    <?php if (! empty($errors['password_confirm'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['password_confirm']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="role" class="form-label">Role</label>
    <select
      class="form-select <?= ! empty($errors['role']) ? 'is-invalid' : '' ?>"
      id="role"
      name="role"
    >
      <option value="member">Member</option>
      <option value="manager">Manager</option>
      <option value="superuser">Super User</option>
    </select>
    <?php if (! empty($errors['role'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['role']) ?></div>
    <?php endif; ?>
  </div>

  <div class="col">
    <button type="submit" class="btn btn-primary">Submit</button>
  </div>
</form>
