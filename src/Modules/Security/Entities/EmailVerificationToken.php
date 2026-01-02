<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Carbon\Carbon;

/**
 * ActiveRecord class for email_verification_tokens table.
 *
 * @property int $email_verification_token_id
 * @property string $user_type
 * @property int $user_id
 * @property string $token_hash
 * @property string | Carbon $expires_at
 * @property string | Carbon $created_at
 */
class EmailVerificationToken extends AbstractEntity
{
    public const UPDATED_AT = null;

    protected $table = 'email_verification_tokens';

    protected $primaryKey = 'email_verification_token_id';
}
