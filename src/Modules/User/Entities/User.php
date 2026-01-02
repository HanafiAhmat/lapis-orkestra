<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Entities;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Abstract User class with common attributes and defination.
 *
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
 * @property string $user_type
 */
abstract class User extends AbstractEntity
{
    /**
     * @var string
     */
    protected $entity_user_type = '';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = ['password'];

    /**
     * Check if the staff user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // public function existsWithEmail(string $email, int $id = null): bool
    // {
    //     $user = new self();
    //     $user = $user->where('email', $email)
    //         ->find();

    //     if ($user instanceof BaseEntity) {
    //         if ($user->isHydrated() && $user->getId() !== $id) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    protected function userType(): Attribute
    {
        return Attribute::make(get: fn () => $this->entity_user_type);
    }
}
