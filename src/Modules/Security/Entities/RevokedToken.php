<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;

/**
 * @property int $revoked_token_id
 * @property string $jti
 * @property string $user_type
 * @property int $user_id
 * @property string $revoked_at
 */
class RevokedToken extends AbstractEntity
{
    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $table = 'revoked_tokens';

    protected $primaryKey = 'revoked_token_id';
}
