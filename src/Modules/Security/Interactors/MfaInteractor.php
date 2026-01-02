<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Interactors;

use BitSynama\Lapis\Framework\Foundation\Clock;
use BitSynama\Lapis\Framework\Foundation\Security;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Entities\MfaSecret;
use BitSynama\Lapis\Modules\User\Entities\User;
use InvalidArgumentException;
use OTPHP\TOTP;
use function random_int;

class MfaInteractor
{
    public static function upsertSecret(int $userId, string $userType, string $type, string $secret): MfaSecret
    {
        /** @var MfaSecret|null $record */
        $record = MfaSecret::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('type', $type)
            ->first();

        if ($record === null) {
            $record = new MfaSecret();
            $record->user_id = $userId;
            $record->user_type = $userType;
            $record->type = $type;
            $record->secret = $secret;
            $record->enabled = true;
            $record->created_at = Clock::now()->toDateTimeString();
            $record->updated_at = Clock::now()->toDateTimeString();
            $record->save();
        } else {
            $record->secret = $secret;
            $record->updated_at = Clock::now()->toDateTimeString();
            $record->save();
        }

        return $record;
    }

    public static function trustDevice(int $userId, string $userType, string $fingerprint, int $days = 7): void
    {
        $record = new MfaSecret();
        $record->user_id = $userId;
        $record->user_type = $userType;
        $record->type = 'trusted';
        $record->secret = '-';
        $record->device_fingerprint = $fingerprint;
        $record->trusted_until = Clock::now()->addDays($days)->toDateTimeString();
        $record->created_at = Clock::now()->toDateTimeString();
        $record->updated_at = Clock::now()->toDateTimeString();
        $record->save();
    }

    public static function markVerified(MfaSecret $secret): void
    {
        $secret->verified_at = Clock::now()->toDateTimeString();
        $secret->save();
    }

    public static function generateOtp(string $type): string
    {
        return match ($type) {
            'totp' => Security::generateSecureToken(10),
            'email', 'sms' => (string) random_int(100000, 999999),
            default => throw new InvalidArgumentException("Unsupported MFA type: {$type}"),
        };
    }

    public static function validate(string $type, string $providedOtp, MfaSecret $secret): bool
    {
        return match ($type) {
            'totp' => self::validateTotp($providedOtp, $secret->secret),
            'email', 'sms' => $providedOtp === $secret->secret,
            default => false,
        };
    }

    public static function generateTotpSetupUri(string $label, string $secret, string $issuer = 'BitSynama'): string
    {
        if (empty($label) || empty($secret) || empty($issuer)) {
            return '';
        }

        $totp = TOTP::create($secret);
        $totp->setLabel($label);
        $totp->setIssuer($issuer);

        return $totp->getProvisioningUri();
    }

    public static function getLatest(string $userType, int $userId, string $type): MfaSecret|null
    {
        /** @var MfaSecret|null $record */
        $record = MfaSecret::where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->orderByDesc('updated_at')
            ->first();

        return $record;
    }

    public static function markSent(MfaSecret $secret): void
    {
        $secret->last_sent_at = Clock::now()->toDateTimeString();
        $secret->save();
    }

    public static function resolveUser(string $userType, int $userId): User|null
    {
        $user = Lapis::userTypeRegistry()->getUserById($userType, $userId);

        return $user;
    }

    public static function deleteByType(string $userType, int $userId, string $type): bool
    {
        /** @var MfaSecret $record */
        $record = MfaSecret::where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        if ($record) {
            $record->delete();

            return true;
        }

        return false;
    }

    public static function isTrustedDevice(int $userId, string $userType, string $fingerprint): bool
    {
        /** @var MfaSecret|null $record */
        $record = MfaSecret::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('device_fingerprint', $fingerprint)
            ->orderByDesc('trusted_until')
            ->first();

        return $record && $record->trusted_until && Clock::now()->lte($record->trusted_until);
    }

    protected static function validateTotp(string $otp, string $secret): bool
    {
        if (empty($otp) || empty($secret)) {
            return false;
        }

        $totp = TOTP::create($secret);

        return $totp->verify($otp);
    }
}
