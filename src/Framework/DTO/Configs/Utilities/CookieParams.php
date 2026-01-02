<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

class CookieParams
{
    /**
     * @param bool      $secure     For secure connection (https) only or both secure and non-secure
     * @param bool      $httponly   For backend editable only or both backend and frontend
     * @param string    $samesite   Strictness for cookie information manipulation: Strict, Lax
     * @param string    $domain     Domain of cookie origin
     * @param string    $path       Path relative to domain that applies to this cookie
     */
    public function __construct(
        public bool $secure = true,
        public bool $httponly = true,
        public string $samesite = 'Strict',
        public string $domain = 'localhost',
        public string $path = '/'
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        /** @var bool $secure */
        $secure = $data['secure'] ?? true;

        /** @var bool $httponly */
        $httponly = $data['httponly'] ?? true;

        /** @var string $samesite */
        $samesite = $data['samesite'] ?? 'Strict';

        /** @var string $domain */
        $domain = $data['domain'] ?? 'localhost';

        /** @var string $path */
        $path = $data['path'] ?? '/';

        return new self(secure: $secure, httponly: $httponly, samesite: $samesite, domain: $domain, path: $path);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'secure' => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite,
            'domain' => $this->domain,
            'path' => $this->path,
        ];
    }
}
