<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Cache;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CacheConfig;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\CacheAdapterInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

#[ImplementsPSR(
    CacheItemPoolInterface::class,
    psr: 'PSR-6',
    usage: 'Implements CacheItemPoolInterface through Symfony\Component\Cache\Adapter\FilesystemAdapter',
    link: 'https://www.php-fig.org/psr/psr-6/#cacheitempoolinterface'
)]
#[AdapterInfo(type: 'cache', key: 'file', description: 'File system caching')]
final class FileCacheAdapter extends FilesystemAdapter implements CacheAdapterInterface
{
    public function __construct(CacheConfig $options)
    {
        $namespace = $options->namespace ?? '';
        $defaultTtl = $options->default_ttl ?? 0;
        $directory = $options->cache_dir ?? null;
        parent::__construct($namespace, $defaultTtl, $directory);
    }
}
