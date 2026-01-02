<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$csrf_token ??= '';
?>

<?php $this->layout('layouts:public.default', [
    'title' => 'Password Reset Request Form',
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
          <h3 class="mb-4 text-center text-primary">Password Reset Request</h3>

          <?php if (! empty($fail)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars((string) $fail) ?></div>
          <?php endif; ?>

          <?php if (! empty($success)): ?>
            <div class="alert alert-info"><?= htmlspecialchars((string) $success) ?></div>
          <?php endif; ?>

          <form method="post" action="/auth/password-reset-request" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(
                (string) $csrf_token
            ) ?>">
            <input type="hidden" name="type" value="customer">

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
