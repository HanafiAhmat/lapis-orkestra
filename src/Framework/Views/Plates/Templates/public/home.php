<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */
?>

<?php $this->layout('layouts:public.default', [
    'title' => 'Welcome to Lapis Orkestra',
]); ?>

<section class="py-5 bg-light text-center">
  <div class="container">
    <h1 class="display-4 text-primary fw-bold">Lapis Orkestra</h1>
    <p class="lead text-muted">A modern, secure and developer-friendly PHP framework</p>
    <p class="mb-4">Start building your next scalable application today, powered by a clean architecture, robust module system, and powerful security features out-of-the-box.</p>
    <a href="/auth/register" class="btn btn-primary btn-lg me-2">Get Started</a>
    <a href="/docs" class="btn btn-outline-secondary btn-lg">Documentation</a>
  </div>
</section>

<section class="py-5 border-top">
  <div class="container">
    <div class="row text-center">
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <i class="bi bi-shield-lock fs-2 text-primary mb-3"></i>
            <h5 class="card-title">Built-in Security</h5>
            <p class="card-text">Lapis Orkestra comes packed with CSRF protection, device fingerprinting, JWT, MFA and more—all modular and customizable.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <i class="bi bi-gear fs-2 text-primary mb-3"></i>
            <h5 class="card-title">Modular Core</h5>
            <p class="card-text">Every feature is organized as a pluggable module or service, making it easier to extend and override without bloating the core.</p>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <div class="card-body">
            <i class="bi bi-layout-text-sidebar-reverse fs-2 text-primary mb-3"></i>
            <h5 class="card-title">Simple View + API</h5>
            <p class="card-text">Lapis Orkestra supports both HTML views and RESTful JSON APIs, with consistent error handling and response formatting.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-light">
  <div class="container text-center">
    <h2 class="fw-bold mb-4">Ready to Build?</h2>
    <p class="text-muted mb-4">Install vendor plugins or develop your own pluggable Modules. This starter site will evolve as your project grows.</p>
    <a href="/admin" class="btn btn-outline-primary btn-lg">
      <i class="bi bi-box-arrow-in-right me-1"></i> Go to Admin Panel
    </a>
  </div>
</section>
