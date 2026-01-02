<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\SystemMonitor\Services\AuditLogService;
use BitSynama\Lapis\Pipeline\Utilities\RequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function hash;

class DeviceFingerprintMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $request = Lapis::requestUtility();

        // $fingerprint = $request->getHeader('X-Device-Fingerprint');
        // $wasProvided = !empty($fingerprint);

        // if (!$fingerprint) {
        //     $ip = RequestHelper::getIpAddress();
        //     $ua = $request->user_agent;
        //     $fingerprint = hash('sha256', $ip . $ua);
        // }

        // Lapis::varRegistry()->set('device_fingerprint', $fingerprint);

        // // Optional: Log only if custom fingerprint is provided
        // if ($wasProvided) {
        //     AuditLogService::record('Device fingerprint received', [
        //         'fingerprint' => $fingerprint,
        //         'user_agent' => $request->user_agent,
        //         'ip' => RequestHelper::getIpAddress(),
        //     ]);
        // }

        return $handler->handle($request);
    }
}
