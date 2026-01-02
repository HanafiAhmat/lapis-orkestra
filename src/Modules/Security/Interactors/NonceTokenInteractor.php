<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Interactors;

use BitSynama\Lapis\Lapis;
use function bin2hex;
use function random_bytes;

class NonceTokenInteractor
{
    /**
     * Generate a new NONCE token and store in session.
     */
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(20));
        Lapis::sessionUtility()->set('nonce', $token);
        Lapis::sessionUtility()->commit();

        return $token;
    }

    /**
     * Get the current NONCE token from session.
     */
    public static function getToken(): string|null
    {
        /** @var string|null $nonceToken */
        $nonceToken = Lapis::sessionUtility()->has('nonce') ? Lapis::sessionUtility()->get('nonce') : null;

        return $nonceToken;
    }

    /**
     * Clear the NONCE token from session.
     */
    public static function clearToken(): void
    {
        Lapis::sessionUtility()->remove('nonce');
        Lapis::sessionUtility()->commit();
    }
}
