<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use Psr\Http\Server\RequestHandlerInterface;

#[ImplementsPSR(
    RequestHandlerInterface::class,
    psr: 'PSR-15',
    usage: '$handler value uses HTTP Server Request Handler (may be null)',
    link: 'https://www.php-fig.org/psr/psr-15/#21-psrhttpserverrequesthandlerinterface'
)]
class RouteMatch
{
    public const NOT_FOUND = 'NOT_FOUND';

    public const METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';

    public const FOUND = 'FOUND';

    /**
     * @param array<string, mixed> $vars
     * @param array<int, string> $allowedMethods
     * @param array<int, MiddlewareDefinition> $middlewares
     * @param array<int, ResponseFilterDefinition> $filters
     */
    public function __construct(
        public readonly string $status,
        public readonly RequestHandlerInterface|null $handler,
        public readonly array $vars = [],
        public readonly array $allowedMethods = [],
        public readonly array $middlewares = [],
        public readonly array $filters = []
    ) {
    }
}
