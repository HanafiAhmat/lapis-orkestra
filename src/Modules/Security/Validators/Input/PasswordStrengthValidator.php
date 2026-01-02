<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Validators\Input;

use Laminas\Validator\AbstractValidator;
use function count;
use function is_string;
use function preg_match;

class PasswordStrengthValidator extends AbstractValidator
{
    public const ERROR_EXISTS = 'errorExists';

    protected array $messageTemplates = [
        self::ERROR_EXISTS => 'Password must have uppercase letters, lowercase letters and numbers',
    ];

    public function isValid(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        preg_match('/[A-Z]+/', (string) $value, $upperCase);
        preg_match('/[a-z]+/', (string) $value, $lowerCase);
        preg_match('/[0-9]+/', (string) $value, $numbers);

        if (! (count($upperCase) > 0 && count($lowerCase) > 0 && count($numbers) > 0)) {
            $this->error(self::ERROR_EXISTS);
            return false;
        }

        return true;
    }
}
