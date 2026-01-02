<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Contracts;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;

interface TokenInterface
{
    public static function generateAccessToken(AbstractEntity $user, string $audience): string;

    public static function generateRefreshToken(): string;

    public static function verifyAccessToken(string $token): JwtPayloadDefinition|null;
}
