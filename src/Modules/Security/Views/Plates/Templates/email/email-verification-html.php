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
    'title' => 'Email Verification',
]); ?>

<p>Please verify your email using this link: <a href="<?= $baseUrl; ?>/auth/email-verification?token=<?= $token; ?>"><?= $baseUrl; ?>/auth/email-verification?token=<?= $token; ?></a></p>