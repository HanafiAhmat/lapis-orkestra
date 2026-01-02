<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Enums;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;

enum AuthState: string implements LapisEnumInterface
{
    case Disabled = 'disabled';            // Auth module is not enabled
    case Unauthenticated = 'unauthenticated'; // Auth module enabled but no user found
    case Authenticated = 'authenticated';  // Token is valid and user is resolved

    public static function default(): self
    {
        return self::Disabled;
    }
}
