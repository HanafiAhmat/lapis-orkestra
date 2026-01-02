<?php declare(strict_types=1); 

$ds = DIRECTORY_SEPARATOR;

require(__DIR__ . $ds . '..' . $ds . 'vendor' . $ds . 'autoload.php');

\BitSynama\Lapis\Lapis::run();
