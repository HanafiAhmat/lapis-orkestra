<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Middlewares;

use BitSynama\Lapis\Modules\SystemMonitor\Services\AuditLogService;
use BitSynama\Lapis\Pipeline\Services\GeoIpService;
use BitSynama\Lapis\Pipeline\Utilities\Constants;
use BitSynama\Lapis\Pipeline\Utilities\Loader;
use BitSynama\Lapis\Pipeline\Utilities\MultiResponse;
use BitSynama\Lapis\Pipeline\Utilities\RequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function in_array;

class IpAccessMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // $ip = RequestHelper::getIpAddress();
        // $config = Loader::config();

        // $allowedIps = $config->get('app.ip_access.allowed_ips', []);
        // $blockedIps = $config->get('app.ip_access.blocked_ips', []);

        // $geo = GeoIpService::lookup($ip);
        // $locationLabel = $geo['city'] ?? $geo['country'] ?? 'Unknown';
        // $ispLabel = $geo['isp'] ?? 'Unknown ISP';
        // $locationNote = "$locationLabel - $ispLabel";

        // if (!empty($blockedIps) && in_array($ip, $blockedIps, true)) {
        //     AuditLogService::record('Blocked IP attempted access', [
        //         'ip' => $ip,
        //         'reason' => 'IP is in blocked list',
        //         'location' => $locationNote,
        //     ]);
        //     MultiResponse::fail('Access Forbidden - IP Blocked', ['ip' => $ip], Constants::STATUS_CODE_FORBIDDEN);
        //     return false;
        // }

        // if (!empty($allowedIps) && !in_array($ip, $allowedIps, true)) {
        //     AuditLogService::record('Denied IP not on whitelist', [
        //         'ip' => $ip,
        //         'reason' => 'IP not in allowed list',
        //         'location' => $locationNote,
        //     ]);
        //     MultiResponse::fail('Access Forbidden - IP Not Allowed', ['ip' => $ip], Constants::STATUS_CODE_FORBIDDEN);
        //     return false;
        // }

        return $handler->handle($request);
    }
}
