<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */
?>

<?php $this->layout('layouts:email.html', [
    'title' => 'Email Testing from Lapis Orkestra',
]); ?>

<p>This is a <strong>test email</strong> from the <em>SystemMonitor</em> module.</p>