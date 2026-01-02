<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

use function is_array;
use function is_string;

class ViewConfig
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public string $adapter = 'plates',
        public ViewOutputsEnabled $outputs_enabled = new ViewOutputsEnabled(),
        public array $extra = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $oeRaw = $data['outputs_enabled'] ?? null;
        $oe = is_array($oeRaw) ? $oeRaw : [];

        $outputsEnabled = new ViewOutputsEnabled(
            html: (bool) ($oe['html'] ?? true),
            json: (bool) ($oe['json'] ?? true),
        );

        $adapterRaw = $data['adapter'] ?? null;
        $adapter = is_string($adapterRaw) ? $adapterRaw : 'plates';

        $extraRaw = $data['extra'] ?? null;
        $extra = is_array($extraRaw) ? $extraRaw : [];

        return new self(adapter: $adapter, outputs_enabled: $outputsEnabled, extra: $extra);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'adapter' => $this->adapter,
            'outputs_enabled' => [
                'html' => $this->outputs_enabled->html,
                'json' => $this->outputs_enabled->json,
            ],
            'extra' => $this->extra,
        ];
    }
}
