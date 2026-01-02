<?php declare(strict_types=1); 

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$baseUrl ??= '';
$token ??= '';
?>

<?php $this->layout('layouts:email.text', [
    'title' => 'Password Reset Request',
]); ?>

Please reset your password using this link: <?= $baseUrl; ?>/auth/password-reset-confirmation?token=<?= $token; ?>
