<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

class CookieConfig
{
    public function __construct(
        public string $adapter = 'lapis',
        public CookieParams $params = new CookieParams()
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $params */
        $params = $data['params'] ?? [];

        /** @var string $adapter */
        $adapter = $data['adapter'] ?? 'lapis';

        return new self(
            adapter: $adapter,
            params: ! empty($params) ? CookieParams::fromArray($params) : new CookieParams()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'adapter' => $this->adapter,
            'params' => $this->params->toArray(),
        ];
    }
}
