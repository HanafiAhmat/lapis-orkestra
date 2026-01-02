<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Cookie;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\CookieAdapterInterface;
use function setcookie;
use function time;

#[AdapterInfo(type: 'cookie', key: 'lapis', description: 'Default Cookie handler')]
final class LapisCookieAdapter implements CookieAdapterInterface
{
    public function set(string $name, string $value, int $expires, bool $httponly = true): void
    {
        /** @var bool $useSecure */
        $useSecure = Lapis::configRegistry()->get('utility.cookie.params.use_secure');

        /** @var string $domain */
        $domain = Lapis::configRegistry()->get('utility.cookie.params.domain');

        $options = [
            'expires' => $expires,
            'httponly' => $httponly,
            'secure' => $useSecure,
            'domain' => $domain,
            'path' => '/',
            'samesite' => 'Strict',
        ];

        setcookie($name, $value, $options);
    }

    public function get(string $name): mixed
    {
        return $_COOKIE[$name] ?? null;
    }

    public function delete(string $name): void
    {
        /** @var string $domain */
        $domain = Lapis::configRegistry()->get('utility.cookie.params.domain');

        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => $domain,
        ]);
    }
}
