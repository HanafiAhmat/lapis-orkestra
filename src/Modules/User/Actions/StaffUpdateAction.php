<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Actions;

use BitSynama\Lapis\Framework\Exceptions\NotFoundException;
use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Modules\User\Checkers\StaffUpdateChecker;
use BitSynama\Lapis\Modules\User\Entities\Staff;
use BitSynama\Lapis\Modules\User\Enums\StaffRole;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use function is_array;
use function password_hash;
use const PASSWORD_BCRYPT;

class StaffUpdateAction
{
    /**
     * @var array<string, mixed>
     */
    protected array $data;

    /**
     * @param array<string, mixed>|object|null $data
     */
    public function __construct(
        array|object|null $data,
        protected int|string $id
    ) {
        $this->data = is_array($data) ? $data : [];
        $this->id = $id;
    }

    public function handle(): Staff
    {
        $user = new Staff();
        $user = $user->find($this->id);
        if (! ($user instanceof Staff)) {
            throw new NotFoundException('Staff not found.');
        }

        $checker = new StaffUpdateChecker();
        if (! $checker->isValid($this->data)) {
            throw new ValidationException($checker->getErrors());
        }

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

        if (empty($user->role)) {
            $user->role = StaffRole::MEMBER->value;
        }

        if (empty($user->status)) {
            $user->status = UserStatus::ACTIVE->value;
        }

        $user->update();

        return $user;
    }
}
