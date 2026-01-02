<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Factories;

use BitSynama\Lapis\Modules\SystemMonitor\DTOs\ThrottleStatusDefinition;
use BitSynama\Lapis\Modules\SystemMonitor\Validators\Structure\ThrottleStatusValidator;
use function property_exists;

final class ThrottleStatusFactory
{
    /**
     * @param array<string, mixed> $array
     */
    public static function fromArray(array $array): ThrottleStatusDefinition
    {
        $dto = self::castToDto((object) $array);
        ThrottleStatusValidator::isValid($dto);

        return $dto;
    }

    public static function fromPayload(object $object): ThrottleStatusDefinition
    {
        $dto = self::castToDto($object);
        ThrottleStatusValidator::isValid($dto);

        return $dto;
    }

    private static function castToDto(object $data): ThrottleStatusDefinition
    {
        $dto = new ThrottleStatusDefinition();

        foreach ((array) $data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value;
            }
        }

        return $dto;
    }
}
