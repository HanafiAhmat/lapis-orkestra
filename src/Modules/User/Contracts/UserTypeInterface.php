<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Contracts;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;
use BitSynama\Lapis\Modules\User\Entities\User;

interface UserTypeInterface
{
    public static function getId(): string;

    public static function getType(): string; // e.g., "staff"

    /**
     * @return array<int, string>
     */
    public static function getRoles(): array;

    public static function findById(int $id): User|null;

    public static function findByEmail(string $email): User|null;

    /**
     * @param array<string, string> $inputs
     */
    public static function createFromInputs(array $inputs): User;

    /**
     * @return class-string<User>
     */
    public static function getEntityClass(): string;

    /**
     * @return class-string<LapisEnumInterface>
     */
    public static function getStatusEnum(): string;

    /**
     * @return class-string<LapisEnumInterface>
     */
    public static function getRoleEnum(): string;

    // public static function getRoleEnum(): ?string;
    public static function allowRegistration(): bool;

    /**
     * Return true if all persistence requirements are ready (tables exist, etc.)
     */
    public static function isReady(): bool;

    /**
     * @return array<int, string>
     */
    public static function requiredTables(): array;
}
