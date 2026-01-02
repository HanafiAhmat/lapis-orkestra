<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Validators\Structure;

use BitSynama\Lapis\Modules\SystemMonitor\DTOs\ThrottleStatusDefinition;
use InvalidArgumentException;

class ThrottleStatusValidator
{
    public static function isValid(ThrottleStatusDefinition $dto): void
    {
        if ($dto->maxAttempts < 1) {
            throw new InvalidArgumentException('Throttle maxAttempts must be greater than zero');
        }

        if ($dto->decaySeconds < 1) {
            throw new InvalidArgumentException('Throttle decaySeconds must be greater than zero');
        }

        if ($dto->remainingSeconds < 0) {
            throw new InvalidArgumentException('Throttle remainingSeconds cannot be negative');
        }

        if ($dto->attempts < 0) {
            throw new InvalidArgumentException('Throttle attempts cannot be negative');
        }
    }
}
