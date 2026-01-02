<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

interface CookieAdapterInterface
{
    public function set(string $name, string $value, int $expires, bool $httponly = true): void;

    public function get(string $name): mixed;

    public function delete(string $name): void;
}
