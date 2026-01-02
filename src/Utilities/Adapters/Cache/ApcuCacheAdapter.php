<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Cache;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CacheConfig;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\CacheAdapterInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;

#[ImplementsPSR(
    CacheItemPoolInterface::class,
    psr: 'PSR-6',
    usage: 'Implements CacheItemPoolInterface through Symfony\Component\Cache\Adapter\ApcuAdapter',
    link: 'https://www.php-fig.org/psr/psr-6/#cacheitempoolinterface'
)]
#[AdapterInfo(type: 'cache', key: 'apcu', description: 'APCU caching')]
final class ApcuCacheAdapter extends ApcuAdapter implements CacheAdapterInterface
{
    public function __construct(CacheConfig $options)
    {
        $namespace = $options->namespace ?? '';
        $defaultTtl = $options->default_ttl ?? 0;
        parent::__construct($namespace, $defaultTtl);
    }
}
