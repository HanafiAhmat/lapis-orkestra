<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Verifiers;

use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;

class AllowAllVerifier implements ActionAccessVerifierInterface
{
    public function can(string $action): bool
    {
        return true;
    }
}
