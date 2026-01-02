<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\SystemMonitor\Services\AuditLogService;
use BitSynama\Lapis\Modules\SystemMonitor\Services\ThrottleService;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\MultiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use function str_replace;

class RateLimiterMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $method = strtoupper($request->getMethod());
        // if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
        //     return $handler->handle($request);
        // }

        // /** @var \Aura\Session\Segment $sessionSeg */
        // $sessionSeg = $request->getAttribute('session');
        // /** @var CsrfToken $csrf */
        // $csrf = $sessionSeg->getCsrfTokenSegment();

        // // pull the field from form data
        // $parsed = $request->getParsedBody();
        // $provided = is_array($parsed) ? ($parsed['_csrf'] ?? '') : '';

        // if (! $csrf->validateToken((string) $provided)) {
        //     // token invalid or missing
        //     throw new RuntimeException('Invalid CSRF token', 400);
        // }

        return $handler->handle($request);
    }

    /**
     * Apply rate limit per IP address or user/device fingerprint with optional burst handling.
     *
     * @param array<string, mixed> $params
     */
    // public function before(array $params): bool
    // {
    //     $config = Lapis::configRegistry();
    //     $maxRequests = (int) $config->get('app.rate_limit.max_requests', 5);
    //     $decaySeconds = (float) $config->get('app.rate_limit.decay_seconds', 60);
    //     $burstMultiplier = (float) $config->get('app.rate_limit.burst_multiplier', 1.5);

    //     $urlEndpoint = Lapis::requestUtility()->url;
    //     $urlKey = str_replace('/', '-', $urlEndpoint);

    //     // Customize per-endpoint rules
    //     switch ($urlKey) {
    //         case '-security-csrf-token':
    //             $maxRequests = 20;
    //             break;
    //         case '-auth-register':
    //         case '-auth-password-reset-request':
    //         case '-auth-resend-email-verification':
    //             $maxRequests = 10;
    //             break;
    //     }

    //     $cacheKey = ThrottleService::generateCacheKey('rate_limit', $urlEndpoint);
    //     $status = ThrottleService::getThrottleStatus($cacheKey);

    //     $burstLimit = (int) ($maxRequests * $burstMultiplier);

    //     if ($status['attempts'] >= $burstLimit) {
    //         $message = $status['isNearing']
    //             ? 'Too Many Requests — retrying soon'
    //             : 'Too Many Requests (burst limit exceeded)';

    //         AuditLogService::record('Rate limiter triggered (burst)', [
    //             'endpoint' => $urlEndpoint,
    //             'client_type' => Helper::getClientType(),
    //             'attempts' => $status['attempts'],
    //             'burst_limit' => $burstLimit,
    //             'ttl_seconds' => $status['ttl'],
    //             'nearing_expiry' => $status['isNearing'],
    //         ]);

    //         MultiResponse::fail($message, [
    //             'wait_seconds' => $status['ttl'],
    //         ], Constants::STATUS_CODE_TOO_MANY_REQUESTS);
    //         return false;
    //     }

    //     ThrottleService::increment($cacheKey, $status['attempts'], $decaySeconds);
    //     return true;
    // }
}
