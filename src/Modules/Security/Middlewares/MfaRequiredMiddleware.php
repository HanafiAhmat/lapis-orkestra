<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;
use BitSynama\Lapis\Modules\Security\Interactors\SecurityContextInteractor;
use BitSynama\Lapis\Modules\SystemMonitor\Services\AuditLogService;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\MultiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginThrottleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $user = SecurityContextInteractor::getUser();
        // if (! $user) {
        //     MultiResponse::fail('Unauthorized. User not authenticated.', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //     return false;
        // }

        // // Determine MFA type (default: email)
        // $mfaType = $params['type'] ?? 'email';
        // $userType = $user->type;
        // $userId = $user->sub;

        // // Trusted device skip (optional)
        // if (! empty($params['allow_trusted']) && MfaInteractor::isTrustedDevice($userType, $userId)) {
        //     return true;
        // }

        // $secret = MfaInteractor::getLatest($userType, $userId, $mfaType);
        // if (! $secret || empty($secret->verified_at)) {
        //     AuditLogService::record('MFA required but not verified', [
        //         'user_type' => $userType,
        //         'user_id' => $userId,
        //         'type' => $mfaType,
        //     ]);

        //     MultiResponse::fail(
        //         'MFA verification required before continuing.',
        //         ['mfa_required' => true],
        //         Constants::STATUS_CODE_UNAUTHORIZED
        //     );
        //     return false;
        // }

        return $handler->handle($request);
    }
}
