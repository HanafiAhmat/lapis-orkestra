<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\MenuItemDefinition;
use BitSynama\Lapis\Framework\Exceptions\RegistryException;
use BitSynama\Lapis\Framework\Exceptions\RegistryItemNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function array_values;
use function count;
use function explode;
use function ksort;
use function str_replace;
use function strtolower;
use function ucwords;
use function usort;
use const PHP_INT_MAX;

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
final class AdminMenuRegistry implements ContainerInterface
{
    /**
     * Stored by section, keyed by item id.
     *
     * @var array<string, array<string, MenuItemDefinition>>
     */
    private array $items = [];

    /**
     * Register a menu item into a section (e.g., 'main', 'secondary', 'additional')
     */
    public function set(string $section, MenuItemDefinition $item, string|null $parentId = null): void
    {
        $this->items[$section] ??= [];

        if ($parentId !== null && $parentId !== '') {
            if (! isset($this->items[$section][$parentId])) {
                $this->items[$section][$parentId] = new MenuItemDefinition(
                    id: $parentId,
                    label: ucwords(str_replace('_', ' ', strtolower($parentId))),
                    children: [
                        $item->id => $item,
                    ]
                );
            } else {
                // children is expected to be array<string, MenuItemDefinition>
                $this->items[$section][$parentId]->children[$item->id] = $item;
            }

            return;
        }

        $this->items[$section][$item->id] = $item;
    }

    public function get(string $sectionWithId): MenuItemDefinition
    {
        $parts = explode('.', $sectionWithId);
        if (count($parts) !== 2) {
            throw new RegistryException("Invalid ID: {$sectionWithId}");
        }

        if (! $this->has($sectionWithId)) {
            throw new RegistryItemNotFoundException("Menu item for ID `{$sectionWithId}` is not found");
        }

        return $this->items[$parts[0]][$parts[1]];
    }

    public function has(string $sectionWithId): bool
    {
        $parts = explode('.', $sectionWithId);
        if (count($parts) !== 2) {
            return false;
        }

        return isset($this->items[$parts[0]][$parts[1]]);
    }

    public function getOrSkip(string $sectionWithId): MenuItemDefinition|false
    {
        return $this->has($sectionWithId) ? $this->get($sectionWithId) : false;
    }

    /**
     * Returns sorted top-level items as a list (for rendering).
     *
     * @return list<MenuItemDefinition>
     */
    public function getBySection(string $section): array
    {
        /** @var array<string, MenuItemDefinition> $itemsById */
        $itemsById = $this->items[$section] ?? [];

        $list = array_values($itemsById);

        usort(
            $list,
            static fn (MenuItemDefinition $a, MenuItemDefinition $b): int =>
                ($a->order ?? PHP_INT_MAX) <=> ($b->order ?? PHP_INT_MAX)
        );

        return $list;
    }

    /**
     * @return array<string, list<MenuItemDefinition>>
     */
    public function all(): array
    {
        $out = [];

        foreach ($this->items as $section => $itemsById) {
            $list = array_values($itemsById);

            usort(
                $list,
                static fn (MenuItemDefinition $a, MenuItemDefinition $b): int =>
                    ($a->order ?? PHP_INT_MAX) <=> ($b->order ?? PHP_INT_MAX)
            );

            $out[$section] = $list;
        }

        ksort($out);
        return $out;
    }
}
