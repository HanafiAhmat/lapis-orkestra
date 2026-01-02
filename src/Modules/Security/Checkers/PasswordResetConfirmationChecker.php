<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Checkers;

use BitSynama\Lapis\Framework\Checkers\AbstractChecker;
use BitSynama\Lapis\Modules\Security\Validators\Input\PasswordIdenticalValidator;
use BitSynama\Lapis\Modules\Security\Validators\Input\PasswordStrengthValidator;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use function array_values;
use function count;

class PasswordResetConfirmationChecker extends AbstractChecker
{
    /**
     * Check if request inputs are valid.
     *
     * @param array<string, mixed> $inputs
     */
    public function isValid(array $inputs): bool
    {
        $tokenValidator = new ValidatorChain();
        $tokenValidator->attach(new NotEmpty([]));

        /** @var string $passwordConfirm */
        $passwordConfirm = $inputs['password_confirm'] ?? '';
        $passwordValidator = new ValidatorChain();
        $passwordValidator->attach(new NotEmpty([]), true);
        $passwordValidator->attach(new StringLength([
            'min' => 8,
            'max' => 64,
        ]), true);
        $passwordValidator->attach(new PasswordStrengthValidator(), true);
        $passwordValidator->attach(new PasswordIdenticalValidator([
            'passwordConfirm' => $passwordConfirm,
        ]), true);

        $isValid = true;

        if (! $tokenValidator->isValid($inputs['token'] ?? '')) {
            $isValid = false;
            $messages = array_values($tokenValidator->getMessages());
            $this->errors['token'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
        }

        if (! $passwordValidator->isValid($inputs['password'] ?? '')) {
            $isValid = false;
            $messages = array_values($passwordValidator->getMessages());
            $this->errors['password'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
        }

        $this->inputs = $inputs;

        return $isValid;
    }
}
