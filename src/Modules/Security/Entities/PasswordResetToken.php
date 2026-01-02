<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Carbon\Carbon;

/**
 * ActiveRecord class for password_reset_tokens table.
 *
 * @property int $password_reset_token_id
 * @property string $user_type
 * @property int $user_id
 * @property string $token_hash
 * @property string $client_type
 * @property string $user_agent
 * @property string $ip_address
 * @property string | Carbon $expires_at
 * @property string | Carbon $created_at
 */
class PasswordResetToken extends AbstractEntity
{
    public const UPDATED_AT = null;

    protected $table = 'password_reset_tokens';

    protected $primaryKey = 'password_reset_token_id';
}
