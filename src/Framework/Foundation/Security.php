<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use BitSynama\Lapis\Lapis;
use function bin2hex;
use function hash;
use function max;
use function random_bytes;
use function round;

/**
 * The Security class contains methods to be used across the application.
 */
class Security
{
    /**
     * Generate a secure random token.
     */
    public static function generateSecureToken(int $length = 64): string
    {
        $finalLength = (int) round($length / 2);
        return bin2hex(random_bytes(max(1, $finalLength)));
    }

    /**
     * Generate a secure random token.
     *
     * @return array<string, mixed>
     */
    public static function generateDeviceFingerprint(): array
    {
        return [
            'user_agent_hash' => hash('sha256', Lapis::requestUtility()->getUserAgent()),
            'device_info' => Lapis::requestUtility()->getDeviceInfo(), // Optional: pass device name/model/platform from mobile/web client
        ];
    }
}
