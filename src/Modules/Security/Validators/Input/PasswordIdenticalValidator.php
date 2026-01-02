<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Validators\Input;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;

/**
 * @psalm-type AbstractOptions = array{
 *     passwordConfirm?: string,
 *     ...<string, mixed>
 * }
 */
class PasswordIdenticalValidator extends AbstractValidator
{
    public const ERROR_EXISTS = 'errorExists';

    protected string $passwordConfirm;

    protected array $messageTemplates = [
        self::ERROR_EXISTS => 'Password must be the same with password confirm',
    ];

    /**
     * @param AbstractOptions $options
     */
    public function __construct(array $options = [])
    {
        $passwordConfirm = $options['passwordConfirm'] ?? null;

        unset($options['passwordConfirm']);

        if ($passwordConfirm === null) {
            throw new InvalidArgumentException('Option `passwordConfirm` is required');
        }

        $this->passwordConfirm = $passwordConfirm;

        parent::__construct($options);
    }

    public function isValid(mixed $value): bool
    {
        if ($value !== $this->passwordConfirm) {
            $this->error(self::ERROR_EXISTS);
            return false;
        }

        return true;
    }
}
