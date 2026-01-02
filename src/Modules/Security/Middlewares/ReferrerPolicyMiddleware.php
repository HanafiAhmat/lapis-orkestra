<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReferrerPolicyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // // $this->app->response()->header('Referrer-Policy', 'no-referrer-when-downgrade');
        // Lapis::response()->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $handler->handle($request);
    }
}
