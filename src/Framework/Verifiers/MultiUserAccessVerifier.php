<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Verifiers;

use BitSynama\Lapis\Framework\Contracts\ActionAccessVerifierInterface;

class MultiUserAccessVerifier implements ActionAccessVerifierInterface
{
    /**
     * @var ActionAccessVerifierInterface[]
     */
    protected array $verifiers;

    public function __construct(ActionAccessVerifierInterface ...$verifiers)
    {
        $this->verifiers = $verifiers;
    }

    public function can(string $action): bool
    {
        foreach ($this->verifiers as $verifier) {
            if ($verifier->can($action)) {
                return true;
            }
        }
        return false;
    }
}
