<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Contracts;

/**
 * Interface that all input checkers must implement.
 */
interface CheckerInterface
{
    /**
     * Validate inputs.
     *
     * @param array<string, mixed> $inputs
     */
    public function isValid(array $inputs): bool;

    /**
     * Get validation error messages.
     *
     * @return array<string, mixed>
     */
    public function getErrors(): array;

    /**
     * Set the entity being validated (usually for update).
     */
    // public function setEntity(BaseEntity $entity): void;
}
