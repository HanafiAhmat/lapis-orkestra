<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\WidgetDefinition;
use BitSynama\Lapis\Framework\Exceptions\RegistryException;
use BitSynama\Lapis\Framework\Exceptions\RegistryItemNotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use function array_values;
use function count;
use function explode;
use function ksort;
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
final class AdminWidgetRegistry implements ContainerInterface
{
    /**
     * @var array<string, array<string, WidgetDefinition>>
     */
    private array $items = [];

    public function set(string $section, WidgetDefinition $item): void
    {
        $this->items[$section] ??= [];
        $this->items[$section][$item->id] = $item;
    }

    public function get(string $sectionWithId): WidgetDefinition
    {
        $parts = explode('.', $sectionWithId);
        if (count($parts) !== 2) {
            throw new RegistryException("Invalid ID: {$sectionWithId}");
        }

        if (! $this->has($sectionWithId)) {
            throw new RegistryItemNotFoundException("Widget item for ID `{$sectionWithId}` is not found");
        }

        // $parts[0] = section, $parts[1] = id
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

    public function getOrSkip(string $sectionWithId): WidgetDefinition|false
    {
        return $this->has($sectionWithId) ? $this->get($sectionWithId) : false;
    }

    /**
     * @return list<WidgetDefinition>
     */
    public function getBySection(string $section): array
    {
        /** @var array<string, WidgetDefinition> $items */
        $items = $this->items[$section] ?? [];

        // usort needs a list, so reindex first
        $list = array_values($items);

        usort(
            $list,
            static fn (WidgetDefinition $a, WidgetDefinition $b): int =>
                ($a->order ?? PHP_INT_MAX) <=> ($b->order ?? PHP_INT_MAX)
        );

        return $list;
    }

    /**
     * @return array<string, list<WidgetDefinition>>
     */
    public function all(): array
    {
        $out = [];

        foreach ($this->items as $section => $itemsById) {
            $list = array_values($itemsById);

            usort(
                $list,
                static fn (WidgetDefinition $a, WidgetDefinition $b): int =>
                    ($a->order ?? PHP_INT_MAX) <=> ($b->order ?? PHP_INT_MAX)
            );

            $out[$section] = $list;
        }

        ksort($out);
        return $out;
    }
}
