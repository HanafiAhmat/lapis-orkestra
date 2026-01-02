<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

use InvalidArgumentException;
use function array_map;
use function is_array;
use function is_int;
use function is_numeric;
use function is_string;

class MenuItemDefinition
{
    /**
     * @param MenuItemDefinition[]|null $children
     */
    public function __construct(
        public string $id,
        public string $label,
        public string|null $icon = null,
        public string|null $href = null,
        public int|null $order = null,
        public array|null $children = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $idRaw = $data['id'] ?? null;
        if (! is_string($idRaw) || $idRaw === '') {
            throw new InvalidArgumentException("Menu item must include a non-empty string 'id'.");
        }

        $labelRaw = $data['label'] ?? null;
        if (! is_string($labelRaw) || $labelRaw === '') {
            throw new InvalidArgumentException("Menu item must include a non-empty string 'label'.");
        }

        $iconRaw = $data['icon'] ?? null;
        $icon = is_string($iconRaw) ? $iconRaw : null;

        $hrefRaw = $data['href'] ?? null;
        $href = is_string($hrefRaw) ? $hrefRaw : null;

        $orderRaw = $data['order'] ?? null;
        $order = null;
        if (is_int($orderRaw)) {
            $order = $orderRaw;
        } elseif (is_string($orderRaw) && $orderRaw !== '' && is_numeric($orderRaw)) {
            $order = (int) $orderRaw;
        }

        $children = null;
        $childrenRaw = $data['children'] ?? null;
        if (is_array($childrenRaw)) {
            /** @var MenuItemDefinition[] $children */
            $children = array_map(
                static function ($child): MenuItemDefinition {
                    if ($child instanceof MenuItemDefinition) {
                        return $child;
                    }
                    if (is_array($child)) {
                        /** @var array<string, mixed> $child */
                        return MenuItemDefinition::fromArray($child);
                    }
                    throw new InvalidArgumentException('Menu child must be an array or MenuItemDefinition.');
                },
                $childrenRaw
            );
        }

        return new self(
            id: $idRaw,
            label: $labelRaw,
            icon: $icon,
            href: $href,
            order: $order,
            children: $children
        );
    }
}
