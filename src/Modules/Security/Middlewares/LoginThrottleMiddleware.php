<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\SystemMonitor\Services\AuditLogService;
use BitSynama\Lapis\Modules\SystemMonitor\Services\ThrottleService;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\MultiResponse;
use BitSynama\Lapis\Pipeline\Utilities\RequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginThrottleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $config = Lapis::configRegistry();
        // $maxAttempts = (int) $config->get('app.login.max_attempts', 5);
        // $decaySeconds = (float) $config->get('app.login.decay_seconds', 60);
        // $burstMultiplier = (float) $config->get('app.login.burst_multiplier', 1.5);

        // $urlEndpoint = Lapis::requestUtility()->url;
        // $cacheKey = ThrottleService::generateCacheKey('login_throttle', $urlEndpoint);
        // $status = ThrottleService::getThrottleStatus($cacheKey);

        // $burstLimit = (int) ($maxAttempts * $burstMultiplier);

        // if ($status['attempts'] >= $burstLimit) {
        //     $message = $status['isNearing']
        //         ? 'Too Many Login Attempts — retrying soon'
        //         : 'Too Many Login Attempts (burst limit exceeded)';

        //     AuditLogService::record('Login throttled', [
        //         'endpoint' => $urlEndpoint,
        //         'client_type' => RequestHelper::getClientType(),
        //         'fingerprint' => RequestHelper::getIpAddress(),
        //         'attempts' => $status['attempts'],
        //         'burst_limit' => $burstLimit,
        //         'ttl_seconds' => $status['ttl'],
        //         'nearing_expiry' => $status['isNearing'],
        //     ]);

        //     MultiResponse::fail($message, [
        //         'wait_seconds' => $status['ttl'],
        //     ], Constants::STATUS_CODE_TOO_MANY_REQUESTS);
        //     return false;
        // }

        // ThrottleService::increment($cacheKey, $status['attempts'], $decaySeconds);
        return $handler->handle($request);
    }
}
