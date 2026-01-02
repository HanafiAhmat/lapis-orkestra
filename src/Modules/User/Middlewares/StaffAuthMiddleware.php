<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Middlewares;

use BitSynama\Lapis\Framework\Responses\MultiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StaffAuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $staffUserPermissionService = Flight::app()->get('service.staff_user_permission');
        // $staffUser = $staffUserPermissionService->current();

        // if (! $staffUser) {
        //     MultiResponse::fail('Unauthorized: Staff login required', [], 401);
        // }

        return $handler->handle($request);
    }
}
