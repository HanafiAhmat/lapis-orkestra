<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\DTOs;

final class AuditLogEventDefinition
{
    public string $action;

    public int|null $userId = null;

    public string|null $userType = null;

    public string|null $userRole = null;

    public string|null $ipAddress = null;

    public string|null $userAgent = null;

    public string|null $clientType = null;

    /**
     * @var array<string, mixed> Extra data related to the action.
     */
    public array $context = [];
}
