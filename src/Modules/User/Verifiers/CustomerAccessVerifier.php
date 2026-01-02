<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Verifiers;

use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;
use BitSynama\Lapis\Modules\User\Interactors\CustomerPermissionInteractor;

class CustomerAccessVerifier implements ActionAccessVerifierInterface
{
    public function can(string $action): bool
    {
        $user = CustomerPermissionInteractor::current();
        return $user && CustomerPermissionInteractor::hasPermission($user, 'Customer', $action);
    }
}
