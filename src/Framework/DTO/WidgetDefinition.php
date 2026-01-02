<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

use Closure;
use InvalidArgumentException;
use function is_int;
use function is_numeric;
use function is_string;

final class WidgetDefinition
{
    public function __construct(
        public string $id,
        public string|null $title,
        public Closure|string $render,
        public int|null $order = null,
        public string|null $colClass = 'col-md-6 col-lg-4',
        public string|null $containerClass = 'card shadow-sm h-100'
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $idRaw = $data['id'] ?? null;
        if (! is_string($idRaw) || $idRaw === '') {
            throw new InvalidArgumentException("Widget must include a non-empty string 'id'");
        }

        $renderRaw = $data['render'] ?? null;
        if (! is_string($renderRaw) && ! ($renderRaw instanceof Closure)) {
            throw new InvalidArgumentException("Widget must include 'render' as a string or Closure");
        }

        $titleRaw = $data['title'] ?? null;
        $title = is_string($titleRaw) ? $titleRaw : null;

        $orderRaw = $data['order'] ?? null;
        $order = null;
        if (is_int($orderRaw)) {
            $order = $orderRaw;
        } elseif (is_string($orderRaw) && $orderRaw !== '' && is_numeric($orderRaw)) {
            $order = (int) $orderRaw;
        }

        $colClassRaw = $data['colClass'] ?? null;
        $colClass = is_string($colClassRaw) ? $colClassRaw : 'col-md-6 col-lg-4';

        $containerClassRaw = $data['containerClass'] ?? null;
        $containerClass = is_string($containerClassRaw) ? $containerClassRaw : 'card shadow-sm h-100';

        return new self(
            id: $idRaw,
            title: $title,
            render: $renderRaw,
            order: $order,
            colClass: $colClass,
            containerClass: $containerClass,
        );
    }
}
