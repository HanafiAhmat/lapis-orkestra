<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CacheConfig;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\CacheAdapterInterface;
use BitSynama\Lapis\Utilities\Contracts\SimpleCacheAdapterInterface;
use RuntimeException;
use function is_dir;
use function mkdir;
use const DIRECTORY_SEPARATOR;

/**
 * Auto-selects and instantiates a PSR-6 cache adapter based on configuration.
 */
class CacheUtility
{
    private readonly CacheAdapterInterface|SimpleCacheAdapterInterface $adapter;

    public function __construct()
    {
        $this->adapter = $this->discoverAndInstantiate();
    }

    public function getAdapter(): CacheAdapterInterface|SimpleCacheAdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Scans framework and child-app adapter directories for classes annotated with AdapterInfo,
     * and instantiates the one matching $adapterKey.
     */
    private function discoverAndInstantiate(): CacheAdapterInterface|SimpleCacheAdapterInterface
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $tmpDir */
        $tmpDir = Lapis::varRegistry()->get('tmp_dir');

        /** @var CacheConfig $cacheConfig */
        $cacheConfig = Lapis::configRegistry()->get('utility.cache') ?? [];

        /** @var string $adapterKey */
        $adapterKey = $cacheConfig->adapter ?? 'file_simple';

        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.Cache',
            interface: CacheAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'CacheAdapter',
            type: 'cache',
            key: $adapterKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($className)) {
            $className = Atlas::discover(
                dirPath: 'Utilities.Adapters.Cache',
                interface: SimpleCacheAdapterInterface::class,
                attribute: AdapterInfo::class,
                classSuffix: 'CacheAdapter',
                type: 'cache',
                key: $adapterKey,
                repoDir: $repoDir,
                projectDir: $projectDir
            );
        }

        if (empty($className)) {
            throw new RuntimeException("Cache adapter '{$adapterKey}' not found.");
        }

        $defaultCacheDir = $tmpDir . DIRECTORY_SEPARATOR . 'caches';
        /** @var string $cacheDir */
        $cacheDir = $cacheConfig->cache_dir ?: $defaultCacheDir;
        if (! is_dir($cacheDir)) {
            if (! mkdir($cacheDir, 0744, true)) {
                throw new RuntimeException('Failed to create cache directory...');
            }
        }
        $cacheConfig->cache_dir = $cacheDir;

        /** @var CacheAdapterInterface|SimpleCacheAdapterInterface $instance */
        $instance = new $className($cacheConfig);

        return $instance;
    }
}
