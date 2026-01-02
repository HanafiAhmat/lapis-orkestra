<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

use Psr\Cache\CacheItemPoolInterface;

/**
 * A marker interface for PSR-6 cache adapters in Lapis Orkestra.
 */
interface CacheAdapterInterface extends CacheItemPoolInterface
{
}
