<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Exceptions;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

#[ImplementsPSR(
    NotFoundExceptionInterface::class,
    psr: 'PSR-11',
    usage: 'Extends Container Not Found Exception Interface',
    link: 'https://www.php-fig.org/psr/psr-11/#33-psrcontainernotfoundexceptioninterface'
)]
class RegistryItemNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
