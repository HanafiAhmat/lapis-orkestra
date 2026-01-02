<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Exceptions\RegistryException;
use BitSynama\Lapis\Framework\Exceptions\RegistryItemNotFoundException;
use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
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
class MiddlewareRegistry implements ContainerInterface
{
    /**
     * @var array<string, Closure|class-string>
     */
    protected array $middlewares = [];

    /**
     * Set a middleware closure or class-string.
     */
    public function set(string $id, Closure|string $middleware): void
    {
        if (
            (
                ! ($middleware instanceof Closure)
                && ! class_exists($middleware)
            ) || (
                ! ($middleware instanceof Closure)
                && class_exists($middleware)
                && ! in_array(MiddlewareInterface::class, class_implements($middleware), true)
            )
        ) {
            throw new RegistryException("Invalid middleware definition for key: {$id}");
        }

        $this->middlewares[$id] = $middleware;
    }

    /**
     * Retrieve a middleware closure or class-string.
     */
    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("Middleware not found: {$id}");
        }

        return $this->middlewares[$id];
    }

    /**
     * Check if a middleware exists.
     */
    public function has(string $id): bool
    {
        return isset($this->middlewares[$id]);
    }

    /**
     * Retrieve a middleware closure or class-string if it exists.
     */
    public function getOrSkip(string $id): mixed
    {
        return $this->has($id) ? $this->get($id) : false;
    }

    /**
     * Retrieve a middleware by its id.
     */
    public function remove(string $id): void
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("Middleware not found: {$id}");
        }

        unset($this->middlewares[$id]);
    }

    /**
     * @return array<string, Closure|string>
     */
    public function all(): array
    {
        return $this->middlewares;
    }
}
