<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Enums;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;

enum StaffRole: string implements LapisEnumInterface
{
    case SUPERUSER = 'superuser';
    case MANAGER = 'manager';
    case MEMBER = 'member';

    public static function default(): self
    {
        return self::MEMBER;
    }
}
