<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Interactors;

use BitSynama\Lapis\Framework\Foundation\Clock;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Entities\DeviceFingerprint;

class DeviceFingerprintInteractor
{
    public static function remember(int $userId, string $userType, string $fingerprint): void
    {
        /** @var DeviceFingerprint|null $record */
        $record = DeviceFingerprint::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('fingerprint', $fingerprint)
            ->first();

        $now = Clock::now()->toDateTimeString();

        if ($record !== null) {
            $record->last_seen_at = $now;
            $record->save();
        } else {
            $entry = new DeviceFingerprint();
            $entry->user_id = $userId;
            $entry->user_type = $userType;
            $entry->fingerprint = $fingerprint;
            $entry->user_agent = Lapis::requestUtility()->getHeader('user_agent');
            $entry->ip_address = Lapis::requestUtility()->getIpAddress();
            $entry->last_seen_at = $now;
            $entry->created_at = $now;
            $entry->save();
        }
    }

    public static function isRecognized(int $userId, string $userType, string $fingerprint): bool
    {
        return DeviceFingerprint::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('fingerprint', $fingerprint)
            ->first() ? true : false;
    }

    public static function forgetOldDevices(int $userId, string $userType, int $days = 180): void
    {
        $cutoff = Clock::now()->subDays($days)->toDateTimeString();
        $deletedRecords = DeviceFingerprint::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('last_seen_at', '<', $cutoff)
            ->delete();
    }
}
