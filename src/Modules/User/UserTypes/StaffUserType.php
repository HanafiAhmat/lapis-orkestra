<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\UserTypes;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;
use BitSynama\Lapis\Modules\User\Contracts\UserTypeInterface;
use BitSynama\Lapis\Modules\User\Entities\Staff;
use BitSynama\Lapis\Modules\User\Entities\User;
use BitSynama\Lapis\Modules\User\Enums\StaffRole;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use function password_hash;
use const PASSWORD_BCRYPT;

final class StaffUserType implements UserTypeInterface
{
    public static function getId(): string
    {
        return '';
    }

    public static function getType(): string
    {
        return 'staff';
    }

    public static function findById(int $id): User|null
    {
        $user = Staff::where('staff_id', $id)->first();

        return $user instanceof User ? $user : null;
    }

    public static function findByEmail(string $email): User|null
    {
        $user = Staff::where('email', $email)->first();

        return $user instanceof User ? $user : null;
    }

    /**
     * @param array<string, string> $inputs
     */
    public static function createFromInputs(array $inputs): User
    {
        $user = new Staff();
        $user->email = $inputs['email'];
        $user->password = password_hash((string) $inputs['password'], PASSWORD_BCRYPT);
        $user->save();

        return $user;
    }

    /**
     * @return class-string<User>
     */
    public static function getEntityClass(): string
    {
        return Staff::class;
    }

    /**
     * @return class-string<LapisEnumInterface>
     */
    public static function getStatusEnum(): string
    {
        return UserStatus::class;
    }

    /**
     * @return array<int, string>
     */
    public static function getRoles(): array
    {
        return [];
    }

    /**
     * @return class-string<LapisEnumInterface>
     */
    public static function getRoleEnum(): string
    {
        return StaffRole::class;
    }

    public static function allowRegistration(): bool
    {
        return true;
    }

    /**
     * @return array<int, string>
     */
    public static function requiredTables(): array
    {
        return [(new Staff())->getTable()];
    }

    public static function isReady(): bool
    {
        return Staff::tableExists();
    }
}
