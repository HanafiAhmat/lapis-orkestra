<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Interactors;

use BitSynama\Lapis\Framework\Contracts\InteractorInterface;
use BitSynama\Lapis\Modules\Security\Entities\RevokedToken;
use Carbon\Carbon;

class RevokedTokenInteractor implements InteractorInterface
{
    public static function revoke(string $jti, string $userType, int $userId): void
    {
        $revoked = new RevokedToken();
        $revoked->jti = $jti;
        $revoked->user_type = $userType;
        $revoked->user_id = $userId;
        $revoked->revoked_at = Carbon::now()->toDateTimeString();
        $revoked->save();
    }

    public static function isRevoked(string $jti): bool
    {
        $entity = RevokedToken::where('jti', $jti)->first();

        return $entity !== null;
    }
}
