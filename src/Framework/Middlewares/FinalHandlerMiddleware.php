<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Middlewares;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\RouteMatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[ImplementsPSR(
    MiddlewareInterface::class,
    psr: 'PSR-15',
    usage: 'Class implements HTTP Server Middleware Interface',
    link: 'https://www.php-fig.org/psr/psr-15/#22-psrhttpservermiddlewareinterface'
)]
#[ImplementsPSR(
    ServerRequestInterface::class,
    psr: 'PSR-7',
    usage: 'process() function accepts HTTP Server Request Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface'
)]
#[ImplementsPSR(
    RequestHandlerInterface::class,
    psr: 'PSR-15',
    usage: '$handler value uses HTTP Server Request Handler (may be null)',
    link: 'https://www.php-fig.org/psr/psr-15/#21-psrhttpserverrequesthandlerinterface'
)]
#[ImplementsPSR(
    ResponseInterface::class,
    psr: 'PSR-7',
    usage: 'process() function returns HTTP Response Interface',
    link: 'https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface'
)]
class FinalHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly RouteMatch $match
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $request->withAttribute('routeVars', $this->match->vars);

        /** @var RequestHandlerInterface $matchHandler */
        $matchHandler = $this->match->handler;

        return $matchHandler->handle($request);
    }
}
