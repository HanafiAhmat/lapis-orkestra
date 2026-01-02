<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Cache;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CacheConfig;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\SimpleCacheAdapterInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;

/**
 * PSR-16 filesystem-based cache adapter.
 * Wraps our PSR-6 FileCacheAdapter to provide a simple cache interface.
 */
#[ImplementsPSR(
    CacheInterface::class,
    psr: 'PSR-16',
    usage: 'Implements CacheItemPoolInterface through Symfony\Component\Cache\Psr16Cache',
    link: 'https://www.php-fig.org/psr/psr-16/#21-cacheinterface'
)]
#[AdapterInfo(type: 'cache', key: 'file_simple', description: 'File system caching implementing simple cache')]
final class FileSimpleCacheAdapter extends Psr16Cache implements SimpleCacheAdapterInterface
{
    public function __construct(CacheConfig $options)
    {
        // Internally use our PSR-6 filesystem cache
        $psr6Pool = new FileCacheAdapter($options);

        // Pass the PSR-6 pool and default TTL to the Psr16Cache wrapper
        parent::__construct($psr6Pool);
    }
}
