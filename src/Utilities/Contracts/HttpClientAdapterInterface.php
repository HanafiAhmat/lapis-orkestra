<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Marker interface for PSR-18 HTTP Client adapters in Lapis Orkestra.
 * Adapters must implement Psr\Http\Client\ClientInterface.
 */
interface HttpClientAdapterInterface extends ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
