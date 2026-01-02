<?php declare(strict_types=1);

namespace BitSynama\Lapis\Services;

use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Services\Contracts\GeoIpProviderInterface;
use RuntimeException;
use function is_array;
use function md5;

class GeoIpService
{
    private static GeoIpProviderInterface|null $provider = null;

    /**
     * Lookup IP geo info with caching (default: 7 days).
     *
     * @return array<string, string|null>
     */
    public static function lookup(string $ip): array
    {
        // $cache = Lapis::cacheUtility();
        // $key = 'geoip:' . md5($ip);

        // $cached = $cache->retrieve($key);
        // if (is_array($cached)) {
        //     return $cached;
        // }

        $geo = self::resolveProvider()->lookup($ip);
        // $cache->store($key, $geo, 604800); // 7 days
        // dd('$geo', $geo);
        return $geo;
    }

    protected static function resolveProvider(): GeoIpProviderInterface
    {
        if (self::$provider) {
            return self::$provider;
        }

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $providerKey */
        $providerKey = Lapis::configRegistry()->get('service.geoip.provider') ?? 'ipapicom';

        /** @var string $provider */
        $provider = Atlas::discover(
            dirPath: 'Services.Providers.GeoIp',
            interface: GeoIpProviderInterface::class,
            attribute: ProviderInfo::class,
            classSuffix: 'GeoIpProvider',
            type: 'geoip',
            key: $providerKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($provider)) {
            throw new RuntimeException("GeoIp provider '{$providerKey}' not found.");
        }

        /** @var GeoIpProviderInterface $provider */
        $provider = new $provider();
        self::$provider = $provider;

        return self::$provider;
    }
}
