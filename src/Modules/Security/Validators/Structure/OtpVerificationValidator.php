<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Validators\Structure;

use BitSynama\Lapis\Modules\Security\DTO\OtpVerificationDefinition;
use InvalidArgumentException;
use function in_array;

final class OtpVerificationValidator
{
    public static function isValid(OtpVerificationDefinition $dto): void
    {
        if (! in_array($dto->channel, ['sms', 'email'], true)) {
            throw new InvalidArgumentException('Invalid channel provided');
        }

        if (empty($dto->destination)) {
            throw new InvalidArgumentException('Destination is required');
        }

        if (empty($dto->token)) {
            throw new InvalidArgumentException('Token is required');
        }

        if (empty($dto->code)) {
            throw new InvalidArgumentException('OTP code is required');
        }
    }
}
