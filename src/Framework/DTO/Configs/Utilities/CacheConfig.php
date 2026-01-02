<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

use function array_key_exists;
use function is_int;
use function is_numeric;
use function is_scalar;
use function is_string;

final class CacheConfig
{
    public function __construct(
        public string $adapter = 'file_simple',
        public string $namespace = '',
        public int $default_ttl = 3600,
        public string $cache_dir = ''
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $adapter = self::stringOrDefault($data, 'adapter', 'file_simple');
        $namespace = self::stringOrDefault($data, 'namespace', '');
        $defaultTtl = self::intOrDefault($data, 'default_ttl', 3600);
        $cacheDir = self::stringOrDefault($data, 'cache_dir', '');

        return new self(adapter: $adapter, namespace: $namespace, default_ttl: $defaultTtl, cache_dir: $cacheDir);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'adapter' => $this->adapter,
            'namespace' => $this->namespace,
            'default_ttl' => $this->default_ttl,
            'cache_dir' => $this->cache_dir,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function stringOrDefault(array $data, string $key, string $default): string
    {
        if (! array_key_exists($key, $data)) {
            return $default;
        }

        $v = $data[$key];

        // allow scalar values (int/bool/float) and cast them; reject arrays/objects/null
        if (is_scalar($v)) {
            return (string) $v;
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function intOrDefault(array $data, string $key, int $default): int
    {
        if (! array_key_exists($key, $data)) {
            return $default;
        }

        $v = $data[$key];

        if (is_int($v)) {
            return $v;
        }

        if (is_string($v) && $v !== '' && is_numeric($v)) {
            return (int) $v;
        }

        // also accept float/bool as scalar → int if you want:
        if (is_scalar($v) && ! is_string($v)) {
            return (int) $v;
        }

        return $default;
    }
}
