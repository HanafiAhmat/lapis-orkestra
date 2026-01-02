<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$token ??= '';
$csrf_token ??= '';
$masked_email ??= '';
?>

<?php $this->layout('layouts:public.default', [
    'title' => 'Password Reset Confirmation',
]); ?>

<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <?php $redied_user_types ??= []; ?>
      <?php if (! in_array('customer', $redied_user_types, true)): ?>
        <div class="alert alert-warning">Customer user type is not ready. Please run migrations.</div>
      <?php endif; ?>

      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
          <h3 class="mb-4 text-center text-primary">Password Reset Confirmation</h3>

          <?php if (! empty($fail)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) $fail) ?></div>
          <?php endif; ?>

          <?php if (! empty($success)): ?>
            <div class="alert alert-info"><?= htmlspecialchars((string) $success) ?></div>
          <?php endif; ?>

          <form method="post" action="/auth/password-reset-confirmation?token=<?= $token ?>" class="needs-validation" novalidate>
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

        </div>
      </div>

      <div class="row mt-3">
        <div class="col-6">
          <a href="/auth/login">Login</a>
        </div>
        <div class="col-6 text-end">
          <a href="/auth/register">Register</a>
        </div>
      </div>
    </div>
  </div>
</section>
