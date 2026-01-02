<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Middlewares;

use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Blocks access unless the current session’s 'user_type'
 * exactly matches the one you ask for.
 */
final class RequireTypeMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $requiredType
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var string $currentUserType */
        $currentUserType = Lapis::sessionUtility()->get('user_type');
        if ($currentUserType !== $this->requiredType) {
            throw new RuntimeException(
                "Forbidden: type '{$this->requiredType}' required, got '{$currentUserType}'",
                403
            );
        }

        return $handler->handle($request);
    }
}
