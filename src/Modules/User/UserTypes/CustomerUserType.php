<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\UserTypes;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;
use BitSynama\Lapis\Modules\User\Contracts\UserTypeInterface;
use BitSynama\Lapis\Modules\User\Entities\Customer;
use BitSynama\Lapis\Modules\User\Entities\User;
use BitSynama\Lapis\Modules\User\Enums\CustomerRole;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use function password_hash;
use const PASSWORD_BCRYPT;

final class CustomerUserType implements UserTypeInterface
{
    public static function getId(): string
    {
        return '';
    }

    public static function getType(): string
    {
        return 'customer';
    }

    public static function findById(int $id): User|null
    {
        $user = Customer::where('customer_id', $id)->first();

        return $user instanceof User ? $user : null;
    }

    public static function findByEmail(string $email): User|null
    {
        $user = Customer::where('email', $email)->first();

        return $user instanceof User ? $user : null;
    }

    /**
     * @param array<string, string> $inputs
     */
    public static function createFromInputs(array $inputs): User
    {
        $user = new Customer();
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
        return Customer::class;
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
        return CustomerRole::class;
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
        return [(new Customer())->getTable()];
    }

    public static function isReady(): bool
    {
        return Customer::tableExists();
    }
}
