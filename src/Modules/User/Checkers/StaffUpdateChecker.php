<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Checkers;

use BitSynama\Lapis\Framework\Checkers\AbstractChecker;
use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use BitSynama\Lapis\Modules\Security\Validators\Input\PasswordStrengthValidator;
use BitSynama\Lapis\Modules\Security\Validators\Input\UniqueEmailValidator;
use BitSynama\Lapis\Modules\User\Entities\Staff;
use BitSynama\Lapis\Modules\User\Enums\StaffRole;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use Laminas\Validator\BackedEnumValue;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use function array_values;
use function count;

class StaffUpdateChecker extends AbstractChecker
{
    public function __construct(
        /**
         * @var string The Staff entity class (injectable)
         */
        protected string $entityClass = Staff::class
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

        $currentId = 0;
        if ($this->entity instanceof AbstractEntity) {
            $currentId = $this->entity->getId();
        }
        $emailValidator->attach(new UniqueEmailValidator([
            'entityClass' => $this->entityClass,
            'currentId' => $currentId,
        ]));

        $passwordValidator = new ValidatorChain();
        $passwordValidator->attach(new NotEmpty([]), true);
        $passwordValidator->attach(new StringLength([
            'min' => 8,
            'max' => 64,
        ]));
        $passwordValidator->attach(new PasswordStrengthValidator(), true);

        $roleValidator = new ValidatorChain();
        $roleValidator->attach(new BackedEnumValue([
            'enum' => StaffRole::class,
        ]));

        $statusValidator = new ValidatorChain();
        $statusValidator->attach(new BackedEnumValue([
            'enum' => UserStatus::class,
        ]));

        $isValid = true;

        if (isset($inputs['name'])) {
            if (! $nameValidator->isValid($inputs['name'] ?? '')) {
                $isValid = false;
                $messages = array_values($nameValidator->getMessages());
                $this->errors['name'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
            }
        }

        if (isset($inputs['email'])) {
            if (! $emailValidator->isValid($inputs['email'] ?? '')) {
                $isValid = false;
                $messages = array_values($emailValidator->getMessages());
                $this->errors['email'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
            }
        }

        if (isset($inputs['password'])) {
            if (! $passwordValidator->isValid($inputs['password'] ?? '')) {
                $isValid = false;
                $messages = array_values($passwordValidator->getMessages());
                $this->errors['password'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
            }
        }

        if (isset($inputs['role'])) {
            if (! $roleValidator->isValid($inputs['role'] ?? '')) {
                $isValid = false;
                $messages = array_values($roleValidator->getMessages());
                $this->errors['role'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
            }
        }

        if (isset($inputs['status'])) {
            if (! $statusValidator->isValid($inputs['status'] ?? '')) {
                $isValid = false;
                $messages = array_values($statusValidator->getMessages());
                $this->errors['status'] = count($messages) > 0 ? $messages[0] : 'Unknown error';
            }
        }

        $this->inputs = $inputs;

        return $isValid;
    }
}
