<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Carbon\Carbon;

/**
 * ActiveRecord class for refresh_tokens table.
 *
 * @property int $refresh_token_id
 * @property string $user_type
 * @property int|string $user_id
 * @property string $token_hash
 * @property string $client_type
 * @property string $user_agent
 * @property string $ip_address
 * @property string | Carbon $expires_at
 * @property string | Carbon $created_at
 */
class RefreshToken extends AbstractEntity
{
    public const UPDATED_AT = null;

    protected $table = 'refresh_tokens';

    protected $primaryKey = 'refresh_token_id';
}
