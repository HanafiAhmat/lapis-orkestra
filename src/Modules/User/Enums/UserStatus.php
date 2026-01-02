<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Enums;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;

enum UserStatus: string implements LapisEnumInterface
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';

    public static function default(): self
    {
        return self::ACTIVE;
    }
}
