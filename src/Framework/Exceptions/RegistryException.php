<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

#[ImplementsPSR(
    ContainerExceptionInterface::class,
    psr: 'PSR-11',
    usage: 'Extends Container Exception Interface',
    link: 'https://www.php-fig.org/psr/psr-11/#32-psrcontainercontainerexceptioninterface'
)]
class RegistryException extends RuntimeException implements ContainerExceptionInterface
{
}
