<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\HttpClient;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\HttpClientAdapterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpClient\Psr18Client;

/**
 * PSR-18 adapter using Symfony HttpClient as backend.
 */
#[ImplementsPSR(
    ClientInterface::class,
    psr: 'PSR-18',
    usage: 'Implements ClientInterface through Symfony\Component\HttpClient\Psr18Client',
    link: 'https://www.php-fig.org/psr/psr-18/#clientinterface'
)]
#[AdapterInfo(type: 'http_client', key: 'symfony', description: 'Uses Symfony\Component\HttpClient\Psr18Client')]
final class SymfonyHttpClientAdapter implements HttpClientAdapterInterface
{
    private readonly Psr18Client $client;

    public function __construct()
    {
        $this->client = new Psr18Client();
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }
}
