<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Entities;

use Carbon\Carbon;

/**
 * User class for staffs table.
 *
 * @property int $staff_id
 * @property string|null $name
 * @property string $email
 * @property string $password
 * @property string|null $email_verified_at
 * @property string $role
 * @property string $status
 * @property string|null $suspended_at
 * @property string|null $invitation_token
 * @property string|null $invitation_expires_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $entity_user_type
 */
class Staff extends User
{
    protected $table = 'staffs';

    protected $primaryKey = 'staff_id';

    /**
     * @var string
     */
    protected $entity_user_type = 'staff';

    /**
     * Check if the staff user is a superuser.
     */
    public function isSuperuser(): bool
    {
        return $this->role === 'superuser';
    }

    /**
     * Check if the invitation is still valid.
     */
    public function isInvitationValid(): bool
    {
        if (! $this->invitation_token || ! $this->invitation_expires_at) {
            return false;
        }
        return Carbon::now()->lt(Carbon::parse($this->invitation_expires_at));
    }
}
