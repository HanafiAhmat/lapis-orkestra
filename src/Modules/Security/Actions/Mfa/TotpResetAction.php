<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Actions\Mfa;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;

class TotpResetAction extends BaseMfaAction
{
    protected int $userId;

    protected string $userType;

    public function __construct()
    {
        /** @var JwtPayloadDefinition $jwtPayload */
        $jwtPayload = Lapis::varRegistry()->get('jwt_payload');

        $this->userId = $jwtPayload->sub;
        $this->userType = $jwtPayload->type;
    }

    public function handle(): bool
    {
        $deleted = MfaInteractor::deleteByType($this->userType, $this->userId, 'totp');

        $this->audit('TOTP reset by user', [
            'user_id' => $this->userId,
            'user_type' => $this->userType,
        ]);

        return $deleted;
    }
}
