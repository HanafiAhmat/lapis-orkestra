<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Exceptions\RegistryItemNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function array_key_exists;

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
    NotFoundExceptionInterface::class,
    psr: 'PSR-11',
    usage: 'Extended NotFoundExceptionInterface for missing entries',
    link: 'https://www.php-fig.org/psr/psr-11/#33-psrcontainernotfoundexceptioninterface'
)]
final class VarRegistry implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $vars = [];

    /**
     * Store a value by id.
     */
    public function set(string $id, mixed $value): void
    {
        $this->vars[$id] = $value;
    }

    /**
     * Retrieve a value by id.
     */
    public function get(string $id): mixed
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("Var not found: {$id}");
        }

        return $this->vars[$id];
    }

    /**
     * Check if a id exists.
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->vars);
    }

    /**
     * Retrieve a var if it exists.
     */
    public function getOrSkip(string $id): mixed
    {
        return $this->has($id) ? $this->get($id) : false;
    }

    /**
     * Remove a id.
     */
    public function remove(string $id): void
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("Var not found: {$id}");
        }

        unset($this->vars[$id]);
    }

    /**
     * Get all key-value pairs.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->vars;
    }
}
