<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
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
use function is_string;
use function ksort;
use function usort;

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
final class PublicWidgetRegistry implements ContainerInterface
{
    /**
     * Stored as: [section => [ "section-id" => item ]]
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    private array $widgets = [];

    /**
     * @param array<string, mixed> $item
     */
    public function set(string $section, array $item): void
    {
        $rawId = $item['id'] ?? null;
        if (! is_string($rawId) || $rawId === '') {
            throw new RegistryException("Widget item must have a non-empty string 'id'");
        }

        $id = $section . '-' . $rawId;
        $this->widgets[$section][$id] = $item;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $sectionWithId): array
    {
        [$section, $id] = $this->splitKey($sectionWithId);

        if (! isset($this->widgets[$section][$id])) {
            throw new RegistryItemNotFoundException("Widget item for ID `{$sectionWithId}` is not found");
        }

        return $this->widgets[$section][$id];
    }

    public function has(string $sectionWithId): bool
    {
        try {
            [$section, $id] = $this->splitKey($sectionWithId);
        } catch (RegistryException) {
            return false;
        }

        return isset($this->widgets[$section][$id]);
    }

    /**
     * @return array<string, mixed>|false
     */
    public function getOrSkip(string $sectionWithId): array|false
    {
        return $this->has($sectionWithId) ? $this->get($sectionWithId) : false;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getBySection(string $section): array
    {
        $items = $this->widgets[$section] ?? [];

        // usort needs a list
        $list = array_values($items);

        usort(
            $list,
            static fn (array $a, array $b): int =>
                self::toOrder($a['order'] ?? null) <=> self::toOrder($b['order'] ?? null)
        );

        return $list;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function all(): array
    {
        $out = [];

        foreach ($this->widgets as $section => $items) {
            $list = array_values($items);

            usort(
                $list,
                static fn (array $a, array $b): int =>
                    self::toOrder($a['order'] ?? null) <=> self::toOrder($b['order'] ?? null)
            );

            $out[$section] = $list;
        }

        ksort($out);

        return $out;
    }

    /**
     * @return array{0: string, 1: string} [section, fullId]
     */
    private function splitKey(string $sectionWithId): array
    {
        $parts = explode('-', $sectionWithId, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new RegistryException("Invalid ID: {$sectionWithId}");
        }

        $section = $parts[0];
        $fullId = $sectionWithId; // IMPORTANT: keys stored as full "section-id"

        return [$section, $fullId];
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
