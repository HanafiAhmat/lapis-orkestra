<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Contracts;

use BackedEnum;

interface LapisEnumInterface extends BackedEnum
{
    public static function default(): self;
}
