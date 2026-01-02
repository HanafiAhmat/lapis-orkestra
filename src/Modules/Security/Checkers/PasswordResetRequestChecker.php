<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Checkers;

use BitSynama\Lapis\Framework\Checkers\AbstractChecker;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use function array_values;
use function count;

class PasswordResetRequestChecker extends AbstractChecker
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
        $emailValidator->attach(new StringLength([
            'min' => 5,
            'max' => 150,
        ]));
        $emailValidator->attach(new EmailAddress());

        $isValid = true;

        if (! $emailValidator->isValid($inputs['email'] ?? '')) {
            $isValid = false;
            $messages = array_values($emailValidator->getMessages());
            $this->errors['email'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
        }

        $this->inputs = $inputs;

        return $isValid;
    }
}
