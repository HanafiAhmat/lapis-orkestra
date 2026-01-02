<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Interactors;

use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Interactors\SecurityContextInteractor;
use BitSynama\Lapis\Modules\User\Verifiers\CustomerAccessVerifier;
use function array_merge_recursive;
use function file_exists;
use function in_array;

class CustomerPermissionInteractor
{
    public static function current(): JwtPayloadDefinition|null
    {
        $user = Lapis::varRegistry()->has('user') ? Lapis::varRegistry()->get('user') : null;

        return $user instanceof JwtPayloadDefinition ? $user : null;
    }

    public static function hasRole(JwtPayloadDefinition $user, string $role): bool
    {
        if (! SecurityContextInteractor::isSecurityModuleEnabled()) {
            return true; // fallback when Auth is disabled
        }

        return $user->role === $role;
    }

    /**
     * @param array<int, string> $roles
     */
    public static function hasAnyRole(JwtPayloadDefinition $user, array $roles): bool
    {
        if (! SecurityContextInteractor::isSecurityModuleEnabled()) {
            return true; // fallback when Auth is disabled
        }

        return in_array($user->role, $roles, true);
    }

    public static function getAccessVerifier(): ActionAccessVerifierInterface
    {
        return new CustomerAccessVerifier();
    }

    public static function hasPermission(JwtPayloadDefinition $user, string $module, string $permission): bool
    {
        if (! SecurityContextInteractor::isSecurityModuleEnabled()) {
            return true; // fallback when Auth is disabled
        }

        $permissions = self::getPermissions($user, $module);

        return in_array($permission, $permissions, true) || in_array('*', $permissions, true);
    }

    /**
     * @return array<int, string>
     */
    protected static function getPermissions(JwtPayloadDefinition $user, string $module): array
    {
        $permissionsConfig = self::loadPermissionsFile($module);

        return $permissionsConfig[$user->role] ?? [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected static function loadPermissionsFile(string $module): array
    {
        $coreFile = __DIR__ . '/../../../config/customer_permissions.php';
        $moduleFile = __DIR__ . '/../../' . $module . '/customer_permissions.php';

        $permissions = file_exists($coreFile) ? require $coreFile : [];
        if (file_exists($moduleFile)) {
            $modulePermissions = require $moduleFile;
            $permissions = array_merge_recursive($permissions, $modulePermissions);
        }

        return $permissions;
    }
}
