<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Validators\Structure;

use BitSynama\Lapis\Modules\SystemMonitor\DTOs\AuditLogEventDefinition;
use InvalidArgumentException;
use function filter_var;
use function in_array;
use const FILTER_VALIDATE_IP;

final class AuditLogEventValidator
{
    public static function isValid(AuditLogEventDefinition $dto): void
    {
        if ($dto->action === '') {
            throw new InvalidArgumentException('Audit action must not be empty.');
        }

        if ($dto->clientType === '') {
            throw new InvalidArgumentException('Client type must not be empty.');
        }

        if (! in_array($dto->clientType, ['web', 'mobile', 'postman'], true)) {
            throw new InvalidArgumentException('Invalid client type specified.');
        }

        if ($dto->ipAddress !== null && ! filter_var($dto->ipAddress, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address format.');
        }

        if ($dto->userAgent !== null && $dto->userAgent === '') {
            throw new InvalidArgumentException('User agent must not be empty if provided.');
        }
    }
}
