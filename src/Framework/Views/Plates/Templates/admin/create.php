<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */
?>

<?php $this->layout('layouts:admin.default', [
    'title' => $title ?? 'Create new record',
    'backAction' => $back_action ?? ''
]); ?>

<div class="alert alert-warning" role="alert">
  <span>Automatic form creation is currently not supported.</span>
  <br/>
  <span>Please create a dedicated form file for this resource in your project app and define its location using the controller `createTemplate` property.</span>
</div>
