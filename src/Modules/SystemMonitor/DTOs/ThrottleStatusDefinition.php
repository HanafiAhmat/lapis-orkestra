<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\DTOs;

final class ThrottleStatusDefinition
{
    public string $key;

    public int $attempts;

    public int $maxAttempts;

    public int $decaySeconds;

    public int $remainingSeconds;

    public bool $isLocked;
}
