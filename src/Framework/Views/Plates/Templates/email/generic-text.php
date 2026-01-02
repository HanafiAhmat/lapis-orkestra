<?php declare(strict_types=1);

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 * @var string $title
 * @var string $content 
 **/

$title = isset($title) && is_string($title) ? $title : '';
$content = isset($content) && is_string($content) ? $content : '';
?>

<?php $this->layout('layouts:email.text', [
    'title' => $title,
]); ?>

<?= $content ?>
