<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Contracts\InteractorInterface;
use BitSynama\Lapis\Framework\Exceptions\RegistryException;
use BitSynama\Lapis\Framework\Exceptions\RegistryItemNotFoundException;
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
final class InteractorRegistry implements ContainerInterface
{
    /**
     * @var array<string, class-string>
     */
    private array $interactors = [];

    /**
     * Set an interactor class-string.
     */
    public function set(string $id, string $className): void
    {
        if (! class_exists($className)) {
            throw new RegistryException("Invalid interactor definition for key: {$id}");
        }

        if (! in_array(InteractorInterface::class, class_implements($className), true)) {
            throw new RegistryException('Interactor class must implement ' . InteractorInterface::class);
        }

        $this->interactors[$id] = $className;
    }

    /**
     * Retrieve an interactor className.
     */
    public function get(string $id): string
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("Interactor not found: {$id}");
        }

        return $this->interactors[$id];
    }

    /**
     * Check if an interactor exists.
     */
    public function has(string $id): bool
    {
        return isset($this->interactors[$id]);
    }

    /**
     * Retrieve an interactor className if it exists.
     */
    public function getOrSkip(string $id): string|bool
    {
        return $this->has($id) ? $this->get($id) : false;
    }

    /**
     * Remove a key.
     */
    public function remove(string $id): void
    {
        if (! $this->has($id)) {
            throw new RegistryItemNotFoundException("Interactor not found: {$id}");
        }

        unset($this->interactors[$id]);
    }

    /**
     * Get all key-value pairs.
     *
     * @return array<string, class-string>
     */
    public function all(): array
    {
        return $this->interactors;
    }
}
