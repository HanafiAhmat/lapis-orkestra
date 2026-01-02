<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

$baseUrl ??= '';
$token ??= '';
?>
<?php $this->layout('layouts:email.html', [
    'title' => 'Password Reset Request',
]); ?>

<p>Please reset your password using this link: <a href="<?= $baseUrl; ?>/auth/password-reset-confirmation?token=<?= $token; ?>"><?= $baseUrl; ?>/auth/email-verification?token=<?= $token; ?></a></p>