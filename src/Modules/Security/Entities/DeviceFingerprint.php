<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Carbon\Carbon;

/**
 * @property int $device_fingerprint_id
 * @property string $user_type
 * @property int $user_id
 * @property string $fingerprint
 * @property string|null $user_agent
 * @property string|null $ip_address
 * @property string $last_seen_at
 * @property string|Carbon $created_at
 */
class DeviceFingerprint extends AbstractEntity
{
    public const UPDATED_AT = null;

    protected $table = 'device_fingerprints';

    protected $primaryKey = 'device_fingerprint_id';
}
