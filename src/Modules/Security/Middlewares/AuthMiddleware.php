<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Framework\Foundation\Constants;
use BitSynama\Lapis\Framework\Responses\RedirectResponse;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Entities\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use function in_array;
use function str_contains;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @param array<int, string> $allowedTypes
     */
    public function __construct(
        private readonly array $allowedTypes = ['staff']
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var User|null $user */
        $user = $request->getAttribute('user');

        if ($user === null) {
            /** @var string $adminPrefix */
            $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

            /** @var string $currentUrl */
            $currentUrl = Lapis::requestUtility()->getCurrentUrl();

            $loginUrl = '/auth/login';
            if (str_contains($currentUrl, (string) $adminPrefix)) {
                $loginUrl = $adminPrefix . $loginUrl;
            }

            if (! Lapis::requestUtility()->jsonOutputRequested()) {
                Lapis::sessionUtility()->setAlert('error', 'User is not logged in');
                return (new RedirectResponse())($loginUrl);
            }

            // For API or Postman
            throw new RuntimeException('User is not logged in', Constants::STATUS_CODE_FORBIDDEN);
        }

        if (! in_array($user->user_type, $this->allowedTypes, true)) {
            if (! Lapis::requestUtility()->jsonOutputRequested()) {
                Lapis::sessionUtility()->setAlert('error', 'User type is not allowed');
                /** @var string $redirectUrl */
                $redirectUrl = Lapis::requestUtility()->getReferer() ?: Lapis::requestUtility()->getServerUrl();
                return (new RedirectResponse())($redirectUrl);
            }

            throw new RuntimeException('User type is not allowed', Constants::STATUS_CODE_UNAUTHORIZED);
        }

        return $handler->handle($request);
    }
}
