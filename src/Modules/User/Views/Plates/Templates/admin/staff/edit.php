<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$post_action ??= '';
$csrf_token ??= '';
if (empty($record)) {
  $record = [
    'staff_id' => -1,
    'name' => '',
    'email' => '',
    'role' => '',
  ];
}
?>

<?php $this->layout('layouts:admin.default', [
    'title' => 'Edit staff record `' . $record['staff_id'] . '`',
    'backAction' => $back_action ?? ''
]) ?>

<form method="post" action="<?= $post_action; ?>" class="col-md-5">
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
      (string) $csrf_token
  ) ?>">
  <input type="hidden" name="_method" value="put">

  <div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input
      type="text"
      class="form-control <?= ! empty($errors['name']) ? 'is-invalid' : '' ?>"
      id="name"
      name="name"
      value="<?= htmlspecialchars($old['name'] ?? $record['name']) ?>"
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
      value="<?= htmlspecialchars($old['email'] ?? $record['email']) ?>"
      minlength="5"
      maxlength="150"
      required
    >
    <?php if (! empty($errors['email'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['email']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="role" class="form-label">Role</label>
    <select
      class="form-select <?= ! empty($errors['role']) ? 'is-invalid' : '' ?>"
      id="role"
      name="role"
    >
      <option value="member" <?= ($record['role'] == 'member') ? 'selected' : ''; ?>>Member</option>
      <option value="manager" <?= ($record['role'] == 'manager') ? 'selected' : ''; ?>>Manager</option>
      <option value="superuser" <?= ($record['role'] == 'superuser') ? 'selected' : ''; ?>>Super User</option>
    </select>
    <?php if (! empty($errors['role'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['role']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select
      class="form-select <?= ! empty($errors['status']) ? 'is-invalid' : '' ?>"
      id="status"
      name="status"
    >
      <option value="active" <?= ($record['role'] == 'active') ? 'selected' : ''; ?>>Active</option>
      <option value="inactive" <?= ($record['role'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
      <option value="suspended" <?= ($record['role'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
      <option value="banned" <?= ($record['role'] == 'banned') ? 'selected' : ''; ?>>Banned</option>
    </select>
    <?php if (! empty($errors['status'])): ?>
      <div class="invalid-feedback"><?= htmlspecialchars((string) $errors['status']) ?></div>
    <?php endif; ?>
  </div>

  <div class="col">
    <button type="submit" class="btn btn-primary">Submit</button>
  </div>
</form>
