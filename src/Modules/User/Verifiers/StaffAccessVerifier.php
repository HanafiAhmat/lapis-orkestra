<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Verifiers;

use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;
use BitSynama\Lapis\Modules\Security\Interactors\SecurityContextInteractor;
use BitSynama\Lapis\Modules\User\Interactors\StaffPermissionInteractor;

class StaffAccessVerifier implements ActionAccessVerifierInterface
{
    public function can(string $action): bool
    {
        if (! SecurityContextInteractor::isSecurityModuleEnabled()) {
            return true;
        }

        $user = StaffPermissionInteractor::current();
        return $user && StaffPermissionInteractor::hasPermission($user, 'Staff', $action);
    }
}
