<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Checkers;

use BitSynama\Lapis\Framework\Checkers\AbstractChecker;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use function array_values;
use function count;

class LoginChecker extends AbstractChecker
{
    /**
     * Check if request inputs are valid.
     *
     * @param array<string, mixed> $inputs
     */
    public function isValid(array $inputs): bool
    {
        $emailValidator = new ValidatorChain();
        $emailValidator->attach(new NotEmpty([]), true);
        $emailValidator->attach(new EmailAddress());

        $passwordValidator = new ValidatorChain();
        $passwordValidator->attach(new NotEmpty([]), true);

        $isValid = true;

        if (! $emailValidator->isValid($inputs['email'] ?? '')) {
            $isValid = false;
            $messages = array_values($emailValidator->getMessages());
            $this->errors['email'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
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
