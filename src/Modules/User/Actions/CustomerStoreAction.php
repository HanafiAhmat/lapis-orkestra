<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Actions;

use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Modules\User\Checkers\CustomerStoreChecker;
use BitSynama\Lapis\Modules\User\Entities\Customer;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use function is_array;
use function password_hash;
use const PASSWORD_BCRYPT;

class CustomerStoreAction
{
    /**
     * @var array<string, mixed>
     */
    protected array $data;

    /**
     * @param array<string, mixed>|object|null $data
     */
    public function __construct(array|object|null $data)
    {
        $this->data = is_array($data) ? $data : [];
    }

    public function handle(): Customer
    {
        $checker = new CustomerStoreChecker();
        if (! $checker->isValid($this->data)) {
            throw new ValidationException($checker->getErrors());
        }

        $user = new Customer();
        $schemBuilder = $user->getConnection()
            ->getSchemaBuilder();
        /** @var string $value */
        foreach ($this->data as $key => $value) {
            if ($key === 'password') {
                $user->password = password_hash((string) $value, PASSWORD_BCRYPT);
            } elseif ($schemBuilder->hasColumn($user->getTable(), $key)) {
                $user->{$key} = $value;
            }
        }

        if (empty($user->status)) {
            $user->status = UserStatus::ACTIVE->value;
        }

        $user->save();

        return $user;
    }
}
