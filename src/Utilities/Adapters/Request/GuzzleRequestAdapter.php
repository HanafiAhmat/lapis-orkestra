<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Request;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\RequestAdapterInterface;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

#[ImplementsPSR(
    RequestFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'GuzzleHttp\Psr7\HttpFactory implements all PSR-17 factory interfaces',
    link: 'https://github.com/guzzle/psr7/blob/master/src/HttpFactory.php#L17'
)]
#[ImplementsPSR(
    ResponseFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'GuzzleHttp\Psr7\HttpFactory implements all PSR-17 factory interfaces',
    link: 'https://github.com/guzzle/psr7/blob/master/src/HttpFactory.php#L17'
)]
#[ImplementsPSR(
    StreamFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'GuzzleHttp\Psr7\HttpFactory implements all PSR-17 factory interfaces',
    link: 'https://github.com/guzzle/psr7/blob/master/src/HttpFactory.php#L17'
)]
#[ImplementsPSR(
    UriFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'GuzzleHttp\Psr7\HttpFactory implements all PSR-17 factory interfaces',
    link: 'https://github.com/guzzle/psr7/blob/master/src/HttpFactory.php#L17'
)]
#[ImplementsPSR(
    UploadedFileFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'GuzzleHttp\Psr7\HttpFactory implements all PSR-17 factory interfaces',
    link: 'https://github.com/guzzle/psr7/blob/master/src/HttpFactory.php#L17'
)]
#[ImplementsPSR(
    ServerRequestFactoryInterface::class,
    psr: 'PSR-17',
    usage: 'GuzzleHttp\Psr7\HttpFactory implements all PSR-17 factory interfaces',
    link: 'https://github.com/guzzle/psr7/blob/master/src/HttpFactory.php#L17'
)]
#[ImplementsPSR(
    ServerRequestInterface::class,
    psr: 'PSR-7',
    usage: 'ServerRequest::fromGlobals() returns a PSR-7 ServerRequest populated from superglobals',
    link: 'https://stackoverflow.com/a/47356821'  // example usage of fromGlobals()  [oai_citation:0‡stackoverflow.com](https://stackoverflow.com/questions/47356821/how-to-get-cookies-with-guzzle?utm_source=chatgpt.com)
)]
#[AdapterInfo(type: 'request', key: 'guzzle', description: 'Guzzle PSR-17/7 Server Request Adapter')]
final class GuzzleRequestAdapter implements RequestAdapterInterface
{
    public function __construct(
        private readonly HttpFactory $httpFactory = new HttpFactory()
    ) {
    }

    public function fromGlobals(): ServerRequestInterface
    {
        // Populates $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER into a PSR-7 request
        return ServerRequest::fromGlobals();
    }

    public function getHttpFactory(): mixed
    {
        // HttpFactory implements RequestFactoryInterface,
        // ResponseFactoryInterface, StreamFactoryInterface, UriFactoryInterface,
        // UploadedFileFactoryInterface, ServerRequestFactoryInterface
        return $this->httpFactory;
    }
}
