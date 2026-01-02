<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Validators\Structure;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use DateTimeImmutable;
use InvalidArgumentException;
use function in_array;

class JwtPayloadValidator
{
    public static function isValid(JwtPayloadDefinition $payload): void
    {
        $now = new DateTimeImmutable();

        if ($payload->exp < $now->getTimestamp()) {
            throw new InvalidArgumentException('Token expired');
        }

        if ($payload->iat > $now->getTimestamp()) {
            throw new InvalidArgumentException('Token issued in the future');
        }

        /** @var array<int, string> $allowedAud */
        $allowedAud = Lapis::configRegistry()->get('app.allowed_audiences');
        if (! in_array($payload->aud, $allowedAud, true)) {
            throw new InvalidArgumentException('Invalid audience');
        }

        if (isset($payload->fp) && empty($payload->fp)) {
            throw new InvalidArgumentException('Invalid fingerprint value');
        }
    }
}
