<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\ResponseFilter;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Contracts\ResponseFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Adds a custom header with configured name/value to every response.
 */
#[ImplementsPSR(
    ServerRequestInterface::class,
    psr: 'PSR-7',
    usage: 'process() function accepts HTTP Server Request Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface'
)]
#[ImplementsPSR(
    ResponseInterface::class,
    psr: 'PSR-7',
    usage: 'process() function returns HTTP Response Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface'
)]
final class AddHeaderResponseFilter implements ResponseFilterInterface
{
    public function __construct(
        private readonly string $headerName,
        private readonly string $headerValue
    ) {
    }

    public function process(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $response = $response->withHeader($this->headerName, $this->headerValue);

        return $response;
    }
}
