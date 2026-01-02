<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\HttpClient;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\HttpClientAdapterInterface;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-18 adapter using Guzzle (v7+) as the HTTP client.
 */
#[ImplementsPSR(
    ClientInterface::class,
    psr: 'PSR-18',
    usage: 'Implements ClientInterface through GuzzleHttp\Client',
    link: 'https://www.php-fig.org/psr/psr-18/#clientinterface'
)]
#[AdapterInfo(type: 'http_client', key: 'guzzle', description: 'Uses GuzzleHttp\Client')]
final class GuzzleHttpClientAdapter implements HttpClientAdapterInterface
{
    private readonly GuzzleClient $client;

    public function __construct()
    {
        $this->client = new GuzzleClient();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // Guzzle 7 implements Psr18 ClientInterface
        return $this->client->sendRequest($request);
    }
}
