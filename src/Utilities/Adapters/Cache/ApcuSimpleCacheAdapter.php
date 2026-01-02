<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Cache;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CacheConfig;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\SimpleCacheAdapterInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Psr16Cache;

/**
 * PSR-16 APCu-based simple cache adapter.
 * Wraps our PSR-6 ApcuCacheAdapter to provide a simple cache interface.
 */
#[ImplementsPSR(
    CacheInterface::class,
    psr: 'PSR-16',
    usage: 'Implements CacheInterface through Symfony\Component\Cache\Psr16Cache',
    link: 'https://www.php-fig.org/psr/psr-16/#21-cacheinterface'
)]
#[AdapterInfo(type: 'cache', key: 'apcu_simple', description: 'APCU caching implementing simple cache')]
final class ApcuSimpleCacheAdapter extends Psr16Cache implements SimpleCacheAdapterInterface
{
    public function __construct(CacheConfig $options)
    {
        // Internally use our PSR-6 apcu cache
        $psr6Pool = new ApcuCacheAdapter($options);

        // Psr16Cache wraps the PSR-6 pool and provides PSR-16 interface
        parent::__construct($psr6Pool);
    }
}
