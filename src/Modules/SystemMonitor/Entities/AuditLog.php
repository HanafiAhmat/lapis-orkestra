<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;

/**
 * @property int $audit_log_id
 * @property string $actor_type
 * @property int $actor_id
 * @property string $action
 * @property string $metadata
 * @property string $ip_address
 * @property string $user_agent
 * @property string $created_at
 */
class AuditLog extends AbstractEntity
{
    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $primaryKey = 'audit_log_id';

    protected $fillable = [];

    public function setActor(string $type, int $id): void
    {
        $this->actor_type = $type;
        $this->actor_id = $id;
    }

    public function getActorKey(): string|null
    {
        return $this->actor_type . ':' . $this->actor_id;
    }
}
