<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Middlewares;

use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Blocks access unless the current session’s 'user_role'
 * exactly matches the one you ask for.
 */
final class RequireRoleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $requiredRole
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var string $currentUserRole */
        $currentUserRole = Lapis::sessionUtility()->get('user_type');
        if ($currentUserRole !== $this->requiredRole) {
            throw new RuntimeException(
                "Forbidden: role '{$this->requiredRole}' required, got '{$currentUserRole}'",
                403
            );
        }

        return $handler->handle($request);
    }
}
