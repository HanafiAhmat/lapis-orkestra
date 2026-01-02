<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Interactors\SecurityContextInteractor;
use BitSynama\Lapis\Modules\SystemMonitor\Services\AuditLogService;
use BitSynama\Lapis\Modules\SystemMonitor\Services\ThrottleService;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\MultiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function str_replace;

class LoginThrottleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // if (!SecurityContextInteractor::isSecurityModuleEnabled()) {
        //     return true; // No Auth = skip user-based throttling
        // }

        // $user = SecurityContextInteractor::getUser();
        // if (! isset($user->sub)) {
        //     MultiResponse::fail('Authentication required', [], Constants::STATUS_CODE_UNAUTHORIZED);
        //     return false;
        // }

        // $urlKey = str_replace('/', '-', Lapis::requestUtility()->url);
        // $cacheKey = "rate_limit_by_user:{$urlKey}:user:{$user->sub}";

        // $maxRequests = $params['max'] ?? 10;
        // $decay = $params['decay'] ?? 60;

        // $throttle = ThrottleService::check($cacheKey, $maxRequests, $decay);
        // if (! $throttle['allowed']) {
        //     AuditLogService::record('Rate limit by user triggered', [
        //         'user_id' => $user->sub,
        //         'endpoint' => Lapis::requestUtility()->url,
        //         'remaining_seconds' => $throttle['wait_seconds'],
        //     ]);

        //     MultiResponse::fail('Rate limit exceeded', [
        //         'retry_after' => $throttle['wait_seconds'],
        //     ], Constants::STATUS_CODE_TOO_MANY_REQUESTS);
        //     return false;
        // }

        return $handler->handle($request);
    }
}
