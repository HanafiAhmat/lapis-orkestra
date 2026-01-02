<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

use function is_array;
use function is_string;

final class SessionConfig
{
    /**
     * @param array<string, SessionVariant> $variants
     */
    public function __construct(
        public string $adapter = 'aura',
        public string $name = '',
        public string $segment = '',
        public array $variants = []
    ) {
        if ($this->variants === []) {
            $this->variants = [
                'default' => SessionVariant::fromArray(self::defaultVariant()),
            ];
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, SessionVariant> $variants */
        $variants = [];

        $rawVariants = $data['variants'] ?? null;
        if (is_array($rawVariants)) {
            /** @var array<string, mixed> $variantConfig */
            foreach ($rawVariants as $variantKey => $variantConfig) {
                if (! is_array($variantConfig)) {
                    continue;
                }
                $variants[$variantKey] = SessionVariant::fromArray($variantConfig);
            }
        }

        $adapter = $data['adapter'] ?? 'aura';
        $name = $data['name'] ?? 'LAPIS_ORKESTRA';
        $segment = $data['segment'] ?? 'LapisOrkestra';

        return new self(
            adapter: is_string($adapter) ? $adapter : 'aura',
            name: is_string($name) ? $name : 'LAPIS_ORKESTRA',
            segment: is_string($segment) ? $segment : 'LapisOrkestra',
            variants: $variants
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $variants = [];

        foreach ($this->variants as $variantKey => $sessionVariant) {
            // $sessionVariant is SessionVariant now (not mixed)
            $variants[$variantKey] = $sessionVariant->toArray();
        }

        return [
            'adapter' => $this->adapter,
            'name' => $this->name,
            'segment' => $this->segment,
            'variants' => $variants,
        ];
    }

    /**
     * @return array{name:string, cookieParams: array<string, mixed>}
     */
    private static function defaultVariant(): array
    {
        return [
            'name' => 'LAPIS_ORKESTRA',
            'cookieParams' => [
                'path' => '/',
                'domain' => 'localhost',
            ],
        ];
    }
}
