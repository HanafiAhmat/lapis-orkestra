<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;
use BitSynama\Lapis\Modules\Security\Interactors\SecurityContextInteractor;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\MultiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EnforceMfaMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $user = SecurityContextInteractor::getUser();
        // $userType = SecurityContextInteractor::getUserType();

        // if (! $user || ! $userType) {
        //     MultiResponse::fail('MFA enforcement requires authenticated user.', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //     return false;
        // }

        // $fingerprint = Lapis::varRegistry()->get('device_fingerprint') ?? null;
        // if ($fingerprint && MfaInteractor::isTrustedDevice($user->sub, $userType, $fingerprint)) {
        // return true; // Device is still trusted, skip MFA
        // }

        // MultiResponse::fail('MFA verification required', [], Constants::STATUS_CODE_UNAUTHORIZED);
        // return false;
        return $handler->handle($request);
    }
}
