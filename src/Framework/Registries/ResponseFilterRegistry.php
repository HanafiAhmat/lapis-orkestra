<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Contracts\ResponseFilterInterface;
use BitSynama\Lapis\Framework\Exceptions\RegistryException;
use BitSynama\Lapis\Framework\Exceptions\RegistryItemNotFoundException;
use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function class_exists;
use function class_implements;
use function in_array;

#[ImplementsPSR(
    ContainerInterface::class,
    psr: 'PSR-11',
    usage: 'Implements from Container Interface',
    link: 'https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface'
)]
#[ImplementsPSR(
    ContainerInterface::class,
    psr: 'PSR-11',
    usage: 'Implemented get() method',
    link: 'https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface'
)]
#[ImplementsPSR(
    ContainerInterface::class,
    psr: 'PSR-11',
    usage: 'Implemented has() method',
    link: 'https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface'
)]
#[ImplementsPSR(
    ContainerExceptionInterface::class,
    psr: 'PSR-11',
    usage: 'Extended ContainerExceptionInterface for registry errors',
    link: 'https://www.php-fig.org/psr/psr-11/#32-psrcontainercontainerexceptioninterface'
)]
#[ImplementsPSR(
    NotFoundExceptionInterface::class,
    psr: 'PSR-11',
    usage: 'Extended NotFoundExceptionInterface for missing entries',
    link: 'https://www.php-fig.org/psr/psr-11/#33-psrcontainernotfoundexceptioninterface'
)]
class ResponseFilterRegistry implements ContainerInterface
{
    /**
     * @var array<string, Closure|class-string>
     */
    protected array $filters = [];

    /**
     * Set a filter closure or class-string.
     */
    public function set(string $id, Closure|string $filter): void
    {
        if (
            (
                ! ($filter instanceof Closure)
                && ! class_exists($filter)
            ) || (
                ! ($filter instanceof Closure)
                && class_exists($filter)
                && ! in_array(ResponseFilterInterface::class, class_implements($filter), true)
            )
        ) {
            throw new RegistryException("Invalid response filter definition for key: {$id}");
        }

        $this->filters[$id] = $filter;
    }

    /**
     * Retrieve a filter closure or class-string.
     */
    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("ResponseFilter not found: {$id}");
        }

        return $this->filters[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->filters[$id]);
    }

    /**
     * Retrieve a filter by its id.
     */
    public function remove(string $id): void
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("ResponseFilter not found: {$id}");
        }

        unset($this->filters[$id]);
    }

    /**
     * @return array<string, Closure|string>
     */
    public function all(): array
    {
        return $this->filters;
    }
}
