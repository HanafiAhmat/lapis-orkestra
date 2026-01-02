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
use function is_int;
use function is_numeric;
use function ksort;
use function str_replace;
use function strtolower;
use function ucwords;
use function usort;

#[ImplementsPSR(
    ContainerInterface::class,
    psr: 'PSR-11',
    usage: 'Implements from Container Interface',
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
final class PublicMenuRegistry implements ContainerInterface
{
    /**
     * Stored as: [section => [ "id" => MenuItemDefinition ]]
     *
     * @var array<string, array<string, MenuItemDefinition>>
     */
    private array $items = [];

    /**
     * Register a menu item into a section (e.g., 'main', 'secondary', 'footer')
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
                    ],
                );
                return;
            }

            // parent exists
            $parent = $this->items[$section][$parentId];
            $parent->children[$item->id] = $item;
            return;
        }

        // top-level
        $this->items[$section][$item->id] = $item;
    }

    public function get(string $sectionWithId): MenuItemDefinition
    {
        [$section, $id] = $this->splitKey($sectionWithId);

        if (! isset($this->items[$section][$id])) {
            throw new RegistryItemNotFoundException("Menu item for ID `{$sectionWithId}` is not found");
        }

        return $this->items[$section][$id];
    }

    public function has(string $sectionWithId): bool
    {
        try {
            [$section, $id] = $this->splitKey($sectionWithId);
        } catch (RegistryException) {
            return false;
        }

        return isset($this->items[$section][$id]);
    }

    public function getOrSkip(string $sectionWithId): MenuItemDefinition|false
    {
        return $this->has($sectionWithId) ? $this->get($sectionWithId) : false;
    }

    /**
     * @return list<MenuItemDefinition>
     */
    public function getBySection(string $section): array
    {
        $assoc = $this->items[$section] ?? [];
        $list = array_values($assoc);

        usort(
            $list,
            static fn (MenuItemDefinition $a, MenuItemDefinition $b): int =>
                self::toOrder($a->order ?? null) <=> self::toOrder($b->order ?? null)
        );

        return $list;
    }

    /**
     * @return array<string, list<MenuItemDefinition>>
     */
    public function all(): array
    {
        $out = [];

        foreach ($this->items as $section => $assoc) {
            $list = array_values($assoc);

            usort(
                $list,
                static fn (MenuItemDefinition $a, MenuItemDefinition $b): int =>
                    self::toOrder($a->order ?? null) <=> self::toOrder($b->order ?? null)
            );

            $out[$section] = $list;
        }

        ksort($out);

        return $out;
    }

    /**
     * @return array{0: string, 1: string} [section, id]
     */
    private function splitKey(string $sectionWithId): array
    {
        $parts = explode('.', $sectionWithId, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new RegistryException("Invalid ID: {$sectionWithId}");
        }

        return [$parts[0], $parts[1]];
    }

    private static function toOrder(mixed $v): int
    {
        if (is_int($v)) {
            return $v;
        }
        if (is_numeric($v)) {
            return (int) $v;
        }
        return 0;
    }
}
