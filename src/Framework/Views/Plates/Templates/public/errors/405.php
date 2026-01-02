<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */
?>

<?php $this->layout('layouts:public.default', [
    'title' => 'Method not allowed!',
]); ?>

<div class="error-container">
  <div class="error-icon">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z"/>
    </svg>
  </div>
  <h1>Method not allowed</h1>
</div>
