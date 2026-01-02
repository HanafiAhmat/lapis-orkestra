<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Enums;

use BitSynama\Lapis\Framework\Contracts\LapisEnumInterface;

enum CustomerRole: string implements LapisEnumInterface
{
    case BLANK = '';

    public static function default(): self
    {
        return self::BLANK;
    }
}
