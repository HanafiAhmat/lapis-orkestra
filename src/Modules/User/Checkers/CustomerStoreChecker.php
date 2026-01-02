<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Checkers;

use BitSynama\Lapis\Framework\Checkers\AbstractChecker;
use BitSynama\Lapis\Framework\Validators\Input\RecordExistsValidator;
use BitSynama\Lapis\Modules\Security\Validators\Input\PasswordIdenticalValidator;
use BitSynama\Lapis\Modules\Security\Validators\Input\PasswordStrengthValidator;
use BitSynama\Lapis\Modules\Security\Validators\Input\UniqueEmailValidator;
use BitSynama\Lapis\Modules\User\Entities\Customer;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use function array_values;
use function count;

class CustomerStoreChecker extends AbstractChecker
{
    public function __construct(
        /**
         * @var string The Customer entity class (injectable)
         */
        protected string $entityClass = Customer::class,
        /**
         * @var string The UserStatus entity class (injectable)
         */
        protected string $statusEntityClass = UserStatus::class
    ) {
    }

    /**
     * Check if request inputs are valid.
     *
     * @param array<string, mixed> $inputs
     */
    public function isValid(array $inputs): bool
    {
        $nameValidator = new ValidatorChain();
        $nameValidator->attach(new NotEmpty([]), true);
        $nameValidator->attach(new StringLength([
            'min' => 5,
            'max' => 150,
        ]));

        $emailValidator = new ValidatorChain();
        $emailValidator->attach(new NotEmpty([]), true);
        $emailValidator->attach(new StringLength([
            'min' => 5,
            'max' => 150,
        ]));
        $emailValidator->attach(new EmailAddress());
        $emailValidator->attach(new UniqueEmailValidator([
            'entityClass' => $this->entityClass,
        ]));

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

        $userStatusIdValidator = new ValidatorChain();
        $userStatusIdValidator->attach(new RecordExistsValidator([
            'entityClass' => $this->statusEntityClass,
        ]));

        $isValid = true;

        if (! $nameValidator->isValid($inputs['name'] ?? '')) {
            $isValid = false;
            $messages = array_values($nameValidator->getMessages());
            $this->errors['name'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
        }

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

        if (isset($inputs['user_status_id'])) {
            if (! $userStatusIdValidator->isValid($inputs['user_status_id'] ?? '')) {
                $isValid = false;
                $messages = array_values($userStatusIdValidator->getMessages());
                $this->errors['user_status_id'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
            }
        }

        $this->inputs = $inputs;

        return $isValid;
    }
}
