<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Factories;

use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Validators\Structure\JwtPayloadValidator;
use function property_exists;

final class JwtPayloadFactory
{
    public static function fromPayload(object $payload): JwtPayloadDefinition
    {
        $dto = self::castToDto($payload);
        JwtPayloadValidator::isValid($dto);

        return $dto;
    }

    /**
     * @param array<string, mixed> $array
     */
    public static function fromArray(array $array): JwtPayloadDefinition
    {
        $dto = self::castToDto((object) $array);
        JwtPayloadValidator::isValid($dto);

        return $dto;
    }

    private static function castToDto(object $data): JwtPayloadDefinition
    {
        $dto = new JwtPayloadDefinition();

        foreach ((array) $data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value;
            }
        }

        return $dto;
    }
}
