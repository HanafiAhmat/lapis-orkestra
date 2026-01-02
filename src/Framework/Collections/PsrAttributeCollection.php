<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Collections;

use ArrayIterator;
use BitSynama\Lapis\Framework\DTO\PsrAttribute;
use IteratorAggregate;
use function strlen;
use function substr;
use function uksort;
use function usort;

/**
 * A typed collection of PsrAttribute objects, keyed by the PSR interface name.
 *
 * @implements IteratorAggregate<int, PsrAttribute>
 */
final class PsrAttributeCollection implements IteratorAggregate
{
    /**
     * @var array<int, PsrAttribute>
     */
    private array $items = [];

    /**
     * Add (or merge into) a PsrAttribute entry by its PSR interface key.
     */
    public function add(PsrAttribute $attribute): void
    {
        $this->items[] = $attribute;
    }

    /**
     * Return all PsrAttribute entries.
     *
     * @return PsrAttribute[]
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * IteratorAggregate: allow foreach($collection as $psr => $attr)
     *
     * @return ArrayIterator<int, PsrAttribute>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * IteratorAggregate: allow foreach($collection as $psr => $attr)
     *
     * @return ArrayIterator<string, non-empty-array<string, non-empty-list<PsrAttribute>>>
     */
    public function groupByPsr(): ArrayIterator
    {
        $psrs = [];
        foreach ($this->items as $instance => $psrAttribute) {
            if (isset($psrs[$psrAttribute->psr])) {
                $psrs[$psrAttribute->psr][] = $psrAttribute;
            } else {
                $psrs[$psrAttribute->psr] = [$psrAttribute];
            }
        }

        $newItems = [];
        foreach ($psrs as $psr => $psrAttributes) {
            foreach ($psrAttributes as $psrAttribute) {
                if (isset($newItems[$psr][$psrAttribute->interface])) {
                    $newItems[$psr][$psrAttribute->interface][] = $psrAttribute;
                } else {
                    $newItems[$psr][$psrAttribute->interface] = [$psrAttribute];
                }
            }
        }

        foreach ($newItems as $psr => $interfaces) {
            foreach ($interfaces as $interface => $attributes) {
                usort(
                    $newItems[$psr][$interface],
                    fn ($aAttribute, $bAttribute) => $aAttribute->class <=> $bAttribute->class
                );
            }

            uksort($newItems[$psr], fn ($aInterface, $bInterface) => $aInterface <=> $bInterface);
        }

        uksort($newItems, function ($aPsr, $bPsr) {
            if (strlen(substr($aPsr, 4)) < 2) {
                $aPsr = '0' . substr($aPsr, 4);
            } else {
                $aPsr = substr($aPsr, 4);
            }
            if (strlen(substr($bPsr, 4)) < 2) {
                $bPsr = '0' . substr($bPsr, 4);
            } else {
                $bPsr = substr($bPsr, 4);
            }
            return $aPsr <=> $bPsr;
        });

        return new ArrayIterator($newItems);
    }
}
