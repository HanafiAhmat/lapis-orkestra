<?php declare(strict_types=1);

namespace League\Plates\Template;

use League\Plates\Engine;

/**
 * Plates Template stub for PHPStan.
 *
 * @property mixed $nonce_token
 * @property mixed $title
 * @property mixed $data
 */
class Template
{
    public const SECTION_MODE_REWRITE = 1;
    public const SECTION_MODE_PREPEND = 2;
    public const SECTION_MODE_APPEND  = 3;

    /**
     * @param array<string, mixed>|null $data
     * @return array<string, mixed>|void
     */
    public function data(?array $data = null): array|void {}

    public function exists(): bool {}

    public function path(): string {}

    /**
     * @param array<string, mixed> $data
     */
    public function render(array $data = []): string {}

    /**
     * @param array<string, mixed> $data
     */
    public function layout(string $name, array $data = []): void {}

    public function start(string $name): void {}

    public function push(string $name): void {}

    public function unshift(string $name): void {}

    public function stop(): void {}

    public function end(): void {}

    public function section(string $name, ?string $default = null): ?string {}

    /**
     * @param array<string, mixed> $data
     */
    public function fetch(string $name, array $data = []): string {}

    /**
     * @param array<string, mixed> $data
     */
    public function insert(string $name, array $data = []): void {}

    /**
     * @param mixed $var
     */
    public function batch(mixed $var, string $functions): mixed {}

    public function escape(?string $string, ?string $functions = null): string {}

    public function e(?string $string, ?string $functions = null): string {}
}
