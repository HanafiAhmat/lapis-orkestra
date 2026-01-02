<?php declare(strict_types=1);

namespace BitSynama\Lapis\Services\Contracts;

interface GeoIpProviderInterface
{
    /**
     * Lookup IP address and return relevant geo and ISP data.
     *
     * @return array<string, string|null>
     *         Example: [ 'city' => 'Singapore', 'country' => 'SG', 'isp' => 'StarHub Ltd.' ]
     */
    public function lookup(string $ip): array;
}
