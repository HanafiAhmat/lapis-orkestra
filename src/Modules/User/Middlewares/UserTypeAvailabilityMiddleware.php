<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Middlewares;

use BitSynama\Lapis\Framework\Exceptions\DbNotInitialisedException;
use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserTypeAvailabilityMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $readiedUserTypes = Lapis::userTypeRegistry()->aliasesReadied();

        // If none are ready, render a setup-needed page
        if (empty($readiedUserTypes)) {
            throw new DbNotInitialisedException();
        }

        return $handler->handle($request);
    }
}
