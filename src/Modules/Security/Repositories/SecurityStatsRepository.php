<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Repositories;

// use BitSynama\Lapis\Entities\RevokedToken;
// use BitSynama\Lapis\Entities\FailedMfaAttempt;
// use BitSynama\Lapis\Entities\BlockedIp;
// use BitSynama\Lapis\Entities\ExpiredToken;

use BitSynama\Lapis\Modules\Security\Entities\DeviceFingerprint;
use BitSynama\Lapis\Modules\Security\Entities\EmailVerificationToken;
use BitSynama\Lapis\Modules\Security\Entities\MfaSecret;
use BitSynama\Lapis\Modules\Security\Entities\PasswordResetToken;
use BitSynama\Lapis\Modules\Security\Entities\RefreshToken;
use BitSynama\Lapis\Modules\Security\Entities\RevokedToken;
use function array_map;

final class SecurityStatsRepository
{
    /**
     * @return array<string, int>|bool
     **/
    public static function securityStats(): array|bool
    {
        $tableExistence = [
            EmailVerificationToken::tableExists(),
            PasswordResetToken::tableExists(),
            RefreshToken::tableExists(),
            RevokedToken::tableExists(),
            DeviceFingerprint::tableExists(),
            MfaSecret::tableExists(),
        ];
        $allTableExists = true;
        array_map(function ($exists) use (&$allTableExists) {
            if (! $exists) {
                $allTableExists = false;
            }
        }, $tableExistence);

        if (! $allTableExists) {
            return false;
        }

        return [
            'pending_email_verifications' => EmailVerificationToken::whereNull('verified_at')->count(),
            'password_resets' => PasswordResetToken::countWithinDays(7),
            'active_refresh_tokens' => RefreshToken::countWithinDays(7),
            'revoked_tokens' => RevokedToken::countWithinDays(7, 'revoked_at'),
            'device_fingerprints' => DeviceFingerprint::distinct()->count('device_hash'),
            'mfa_enabled_users' => MfaSecret::count(),
        ];
    }

    /**
     * @return array<string, int>|bool
     **/
    public static function authSessionStats(): array|bool
    {
        $tableExistence = [RefreshToken::tableExists(), RevokedToken::tableExists()];
        $allTableExists = true;
        array_map(function ($exists) use (&$allTableExists) {
            if (! $exists) {
                $allTableExists = false;
            }
        }, $tableExistence);

        if (! $allTableExists) {
            return false;
        }

        return [
            'active_sessions' => RefreshToken::count(),
            'new_sessions_7d' => RefreshToken::countWithinDays(7),
            'revoked_sessions_7d' => RevokedToken::countWithinDays(7, 'revoked_at'),
        ];
    }
}
