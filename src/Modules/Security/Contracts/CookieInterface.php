<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Contracts;

interface CookieInterface
{
    public static function set(string $name, string $value, int $expires, bool $httponly = true): bool;

    public static function clear(string $name): bool;
}
