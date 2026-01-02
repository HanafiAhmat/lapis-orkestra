<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

use function is_array;
use function is_string;

class SessionVariant
{
    public function __construct(
        public string $name = 'LAPIS_ORKESTRA',
        public CookieParams $cookieParams = new CookieParams(),
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $nameRaw = $data['name'] ?? 'LAPIS_ORKESTRA';
        $name = is_string($nameRaw) && $nameRaw !== '' ? $nameRaw : 'LAPIS_ORKESTRA';

        /** @var array<string, mixed> $cookieParamsRaw */
        $cookieParamsRaw = $data['cookieParams'] ?? [];
        $cookieParams = is_array($cookieParamsRaw)
            ? CookieParams::fromArray($cookieParamsRaw)
            : new CookieParams();

        return new self(name: $name, cookieParams: $cookieParams);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'cookieParams' => $this->cookieParams->toArray(),
        ];
    }
}
