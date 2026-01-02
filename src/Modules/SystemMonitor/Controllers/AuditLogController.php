<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Controllers;

use BitSynama\Lapis\Framework\DTO\ActionResponse;
// use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;
// use BitSynama\Lapis\Pipeline\Registry\ServiceRegistry;
use BitSynama\Lapis\Modules\SystemMonitor\Entities\AuditLog;
use Psr\Http\Message\ServerRequestInterface;

final class AuditLogController
{
    public function recent(ServerRequestInterface $request): ActionResponse
    {
        // if (! $this->isAuthorized('index')) {
        //     return;
        // }

        $logs = new AuditLog();
        $logs = $logs->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'records' => $logs->toArray(),
            ],
            message: 'API health status',
            template: 'admin.default'
        );
    }

    // protected function resolveAccessVerifier(): ActionAccessVerifierInterface
    // {
    //     if (ServiceRegistry::has('stafuser', 'permission')) {
    //         $accessVerifier = ServiceRegistry::get('stafuser', 'permission')::getAccessVerifier();
    //         return $accessVerifier instanceof ActionAccessVerifierInterface ? $accessVerifier : parent::resolveAccessVerifier();
    //     }

    //     return parent::resolveAccessVerifier();
    // }
}
