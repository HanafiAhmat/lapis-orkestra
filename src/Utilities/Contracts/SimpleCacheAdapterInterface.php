<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

use Psr\SimpleCache\CacheInterface;

/**
 * Marker interface for PSR-16 (Simple Cache) adapters in Lapis Orkestra.
 * All simple–cache adapters must implement Psr\SimpleCache\CacheInterface.
 */
interface SimpleCacheAdapterInterface extends CacheInterface
{
    // No additional methods; this alias exists for framework typing.
}
