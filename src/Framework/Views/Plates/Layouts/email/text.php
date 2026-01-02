<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

/**
 * Plates layout template
 * 
 * @var \League\Plates\Template\Template $this
 */

/** @var string $brandName */
$brandName = Lapis::configRegistry()->get('app.name') ?? 'Lapis Orkestra';
?>

<?= $brandName; ?>

=================================================================


<?= $this->section('content') ?>


=================================================================
© <?= date(
    'Y'
) ?> <?= $brandName; ?>. All rights reserved.
