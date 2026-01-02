<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Factories;

use BitSynama\Lapis\Modules\SystemMonitor\DTOs\AuditLogEventDefinition;
use BitSynama\Lapis\Modules\SystemMonitor\Validators\Structure\AuditLogEventValidator;
use function property_exists;

final class AuditLogEventFactory
{
    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): AuditLogEventDefinition
    {
        $dto = new AuditLogEventDefinition();

        foreach ((array) $payload as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value;
            }
        }

        AuditLogEventValidator::isValid($dto);

        return $dto;
    }
}
