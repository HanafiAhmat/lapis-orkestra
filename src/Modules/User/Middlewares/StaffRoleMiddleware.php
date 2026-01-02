<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Middlewares;

// use BitSynama\Lapis\Pipeline\Registry\ServiceRegistry;
use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Responses\MultiResponse;
use BitSynama\Lapis\Modules\User\Services\StaffUserPermissionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StaffRoleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $securityContext = ServiceRegistry::get('security', 'context');
        // if (
        //     ! $securityContext
        //     || (
        //         $securityContext
        //         && ! $securityContext::isSecurityModuleEnabled()
        //     )
        // ) {
        //     return true;
        // }

        // $staffUser = StaffUserPermissionService::current();

        // if (
        //     ! $staffUser
        //     || ! StaffUserPermissionService::hasAnyRole($staffUser, $this->requiredRoles)
        // ) {
        //     MultiResponse::fail('Forbidden: Insufficient staff role', [], Constants::STATUS_CODE_FORBIDDEN);
        //     return false;
        // }

        return $handler->handle($request);
    }
}
