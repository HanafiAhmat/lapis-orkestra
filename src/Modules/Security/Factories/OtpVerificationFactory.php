<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Factories;

use BitSynama\Lapis\Modules\Security\DTO\OtpVerificationDefinition;
use BitSynama\Lapis\Modules\Security\Validators\Structure\OtpVerificationValidator;

final class OtpVerificationFactory
{
    /**
     * @param array<string, string|null> $data
     */
    public static function fromArray(array $data): OtpVerificationDefinition
    {
        $dto = new OtpVerificationDefinition();

        $dto->channel = $data['channel'] ?? '';
        $dto->destination = $data['destination'] ?? '';
        $dto->token = $data['token'] ?? '';
        $dto->code = $data['code'] ?? '';
        $dto->fingerprint = $data['fingerprint'] ?? null;

        OtpVerificationValidator::isValid($dto);

        return $dto;
    }
}
