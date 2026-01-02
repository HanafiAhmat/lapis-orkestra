<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

interface SessionAdapterInterface
{
    public function setName(string $name): void;

    /**
     * @return array<string, mixed>
     */
    public function getCookieParams(): array;

    /**
     * @param array<string, mixed> $params
     */
    public function setCookieParams(array $params): void;

    public function start(): void;

    public function commit(): void;

    public function getCsrfToken(): string;

    public function setFlash(string $key, mixed $val): void;

    public function getFlash(string $key, mixed $alt = null): mixed;

    public function clearFlash(): void;

    public function setAlert(string $key, mixed $val): void;

    public function getAlert(string $key, mixed $alt = null): mixed;

    public function isCsrfTokenValid(string $token): bool;

    public function has(string $var): bool;

    public function get(string $var, mixed $alt = null): mixed;

    public function set(string $var, mixed $value): void;

    public function remove(string $var): void;
}
