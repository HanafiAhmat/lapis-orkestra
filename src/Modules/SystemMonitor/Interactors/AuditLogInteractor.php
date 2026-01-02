<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Interactors;

use BitSynama\Lapis\Framework\Contracts\InteractorInterface;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\SystemMonitor\Entities\AuditLog;
use function date;
use function json_encode;

class AuditLogInteractor implements InteractorInterface
{
    // private static AuditLog|null $auditLogInstance = null;

    /**
     * For test or injection: override default AuditLog instance.
     * This is used mainly for unit testing or mocking.
     */
    public static function setAuditLogInstance(AuditLog|null $instance): void
    {
        // self::$auditLogInstance = $instance;
    }

    /**
     * Record an audit log action.
     *
     * @param array<string, mixed> $metadata
     */
    public static function record(string $action, array $metadata = []): void
    {
        // // Respect config toggle
        // if (! Loader::config()->get('app.audit_log_enabled')) {
        //     return;
        // }

        // $actor = self::resolveActor();

        // $log = self::$auditLogInstance ?? new AuditLog();
        // if ($actor !== null) {
        //     $log->setActor($actor['type'], $actor['id']);
        // }

        // $log->action = $action;
        // $log->metadata = json_encode($metadata) ?: '{}';
        // $log->ip_address = RequestHelper::getIpAddress();
        // $log->user_agent = RequestHelper::getUserAgent();
        // $log->created_at = date('Y-m-d H:i:s');

        // $log->insert();
    }

    /**
     * Resolve actor info from Lapis var (e.g., staff_user, customer).
     *
     * @return array{type: string, id: int}|null
     */
    protected static function resolveActor(): array|null
    {
        // if (Lapis::varRegistry()->has('user') && Lapis::varRegistry()->get('user')?->sub) {
        //     return [
        //         'type' => Lapis::varRegistry()->get('user')->type,
        //         'id' => Lapis::varRegistry()->get('user')->sub,
        //     ];
        // }

        return null;
    }
}
