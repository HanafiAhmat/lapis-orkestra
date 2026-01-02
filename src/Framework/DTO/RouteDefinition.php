<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use Psr\Http\Server\RequestHandlerInterface;

#[ImplementsPSR(
    RequestHandlerInterface::class,
    psr: 'PSR-15',
    usage: '$handler value uses HTTP Server Request Handler',
    link: 'https://www.php-fig.org/psr/psr-15/#21-psrhttpserverrequesthandlerinterface'
)]
class RouteDefinition
{
    /**
     * @param array<int, MiddlewareDefinition> $middlewares
     * @param array<int, ResponseFilterDefinition> $filters
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly RequestHandlerInterface $handler,
        public readonly array $middlewares = [],   // PSR-15 stack
        public readonly array $filters = []    // ResponseFilter IDs
    ) {
    }
}
