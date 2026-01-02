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
    'title' => 'Email Verification',
]); ?>

Please verify your email using this link: <?= $baseUrl; ?>/auth/email-verification?token=<?= $token; ?>
