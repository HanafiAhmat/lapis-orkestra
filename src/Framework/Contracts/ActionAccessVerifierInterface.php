<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Contracts;

interface ActionAccessVerifierInterface
{
    public function can(string $action): bool;
}
