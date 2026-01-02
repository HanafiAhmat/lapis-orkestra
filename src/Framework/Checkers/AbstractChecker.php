<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Checkers;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;

/**
 * The AbstractChecker class contains required attributes and methods to check request inputs.
 * It is responsible for checking and validating incoming HTTP request inputs.
 */
class AbstractChecker
{
    /**
     * @var array<string, string>  List of errors.
     */
    protected array $errors = [];

    /**
     * @var array<string, mixed>  List of finalised inputs.
     */
    protected array $inputs = [];

    /**
     * @var AbstractEntity  Assigned entity record to be checked with.
     */
    protected AbstractEntity|null $entity = null;

    /**
     * Check if request inputs are valid.
     *
     * @param array<string, mixed> $inputs
     */
    public function isValid(array $inputs): bool
    {
        $this->inputs = $inputs;
        return true;
    }

    /**
     * Inputs validation errors.
     *
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Finalised inputs.
     *
     * @return array<string, mixed>
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /**
     * Check if request inputs are valid.
     *
     * @param AbstractEntity  $entity  Assign entity record to be checked with.
     */
    public function setEntity(AbstractEntity $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * Placeholder to satisfy phpstan.
     * Actual method is in \Laminas\Validator\Callback.
     *
     * @param string  $message  Error message.
     * @param string  $type  Error message type.
     */
    public function setMessage(string $message, string $type): void
    {
    }

    // Optional: helper to add an error
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }
}
