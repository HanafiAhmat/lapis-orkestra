<?php declare(strict_types=1);

namespace BitSynama\Lapis\Services\Providers\GeoIp;

use BitSynama\Lapis\Services\Contracts\GeoIpProviderInterface;
use BitSynama\Lapis\Services\ProviderInfo;
use function file_get_contents;
use function is_array;
use function json_decode;
use function urlencode;

#[ProviderInfo('geoip', 'ipapicom')]
class IpApiComGeoIpProvider implements GeoIpProviderInterface
{
    protected string $endpoint = 'http://ip-api.com/json/';

    public function lookup(string $ip): array
    {
        $url = $this->endpoint . urlencode($ip) . '?fields=status,message,country,city,isp';

        $response = @file_get_contents($url);
        if (! $response) {
            return [
                'country' => null,
                'city' => null,
                'isp' => null,
            ];
        }

        $json = json_decode($response, true);
        if (! is_array($json) || ($json['status'] ?? 'fail') !== 'success') {
            return [
                'country' => null,
                'city' => null,
                'isp' => null,
            ];
        }

        return [
            'country' => $json['country'] ?? null,
            'city' => $json['city'] ?? null,
            'isp' => $json['isp'] ?? null,
        ];
    }
}
