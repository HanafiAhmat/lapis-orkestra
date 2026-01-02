<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Carbon\Carbon;
use function class_exists;

/**
 * ActiveRecord class for `mfa_secrets` table.
 *
 * @property int $mfa_secret_id
 * @property int $user_id
 * @property string $user_type
 * @property string $type
 * @property string $secret
 * @property string $device_fingerprint
 * @property Carbon|string $trusted_until
 * @property Carbon|string|null $last_sent_at
 * @property Carbon|string|null $verified_at
 * @property bool $enabled
 * @property Carbon|string $created_at
 * @property Carbon|string $updated_at
 */
class MfaSecret extends AbstractEntity
{
    protected $table = 'mfa_secrets';

    protected $primaryKey = 'mfa_secret_id';

    /**
     * Return user_type => model mapping if polymorphic relation support is required.
     */
    public function getUserClass(): string|null
    {
        $module = $this->user_type;

        $userClass = "App\\Modules\\{$module}\\Entities\\{{$module}}";
        if (class_exists($userClass)) {
            return $userClass;
        }

        $userClass = "BitSynama\\Lapis\\Modules\\User\\Entities\\{{$module}}";
        if (class_exists($userClass)) {
            return $userClass;
        }

        return null;
    }

    /**
     * Dynamic user accessor
     */
    public function getUser(): AbstractEntity|null
    {
        $userClass = $this->getUserClass();
        if ($userClass) {
            $user = $userClass::find($this->user_id);
            return $user;
        }

        return null;
    }
}
