<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

use BitSynama\Lapis\Framework\DTO\RouteMatch;
use Psr\Http\Message\ServerRequestInterface;

interface RouterAdapterInterface
{
    /**
     * Match the incoming request and return a RouteMatch.
     */
    public function match(ServerRequestInterface $request): RouteMatch;
}
