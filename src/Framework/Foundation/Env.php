<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use InvalidArgumentException;
use function array_filter;
use function array_map;
use function explode;
use function in_array;
use function is_float;
use function is_numeric;
use function is_string;
use function strtolower;
use function trim;

class Env
{
    public static function bool(string $key, bool $default = false): bool
    {
        $value = $_ENV[$key] ?? false;
        if ($value === false) {
            return $default;
        }

        if (is_numeric($value)) {
            return $value === 0 ? false : true;
        }

        if (is_string($value)) {
            $value = strtolower($value);
            return in_array($value, ['1', 'true', 'yes', 'on'], true);
        }

        return $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = $_ENV[$key] ?? false;
        return is_numeric($value) ? (int) $value : $default;
    }

    public static function float(string $key, float $default = 0.0): float
    {
        $value = $_ENV[$key] ?? false;
        return is_float($value) ? (float) $value : $default;
    }

    public static function string(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? false;
        if ($value === false) {
            return $default;
        }

        if (is_string($value)) {
            return $value;
        }

        return $default;
    }

    /**
     * @param array<mixed, mixed> $default
     *
     * @return array<int, mixed>
     */
    public static function array(string $key, array $default = [], string $delimiter = ','): array
    {
        if ($delimiter === '') {
            throw new InvalidArgumentException('Delimiter must not be an empty string.');
        }

        $value = $_ENV[$key] ?? false;
        if ($value === false) {
            return $default;
        }

        if (! is_string($value)) {
            return $default;
        }

        $parts = explode($delimiter, $value);
        $parts = array_map(trim(...), $parts);
        return array_filter($parts, fn ($item) => $item !== '');
    }
}
