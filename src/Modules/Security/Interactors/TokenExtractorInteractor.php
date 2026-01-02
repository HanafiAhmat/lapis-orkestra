<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Interactors;

use BitSynama\Lapis\Lapis;
use function in_array;
use function preg_match;

class TokenExtractorInteractor
{
    /**
     * Extract the access token from the current HTTP request.
     */
    public static function getAccessToken(): string|null
    {
        $requestUtility = Lapis::requestUtility();

        $clientType = $requestUtility->getClientType();
        if ($clientType === 'web') {
            /** @var string|null $cookieAccessToken */
            $cookieAccessToken = Lapis::cookieUtility()->get('access_token');

            return $cookieAccessToken;
        }

        if (in_array($clientType, ['mobile', 'postman'], true)) {
            $authorization = $requestUtility->getHeader('Authorization');
            if (! empty($authorization) && preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract the refresh token from the current HTTP request.
     */
    public static function getRefreshToken(): string|null
    {
        /** @var string|null $cookieRefreshToken */
        $cookieRefreshToken = Lapis::cookieUtility()->get('refresh_token');

        /** @var string $headerRefreshToken */
        $headerRefreshToken = Lapis::requestUtility()->getHeader('Authorization-Refresh');

        return $cookieRefreshToken
            ?? $headerRefreshToken
            ?? null;
    }

    // /**
    //  * Extract the CSRF token from the current HTTP request.
    //  * Supports both form and header extraction.
    //  */
    // public static function getCsrfToken(): string|null
    // {
    //     $requestUtility = Lapis::requestUtility();

    //     // Try form input first (useful for web forms)
    //     $formToken = $requestUtility->data['csrf_token'] ?? null;
    //     if (! empty($formToken)) {
    //         return $formToken;
    //     }

    //     // Fallback to X-Csrf-Token header
    //     return $requestUtility->getHeader('X-Csrf-Token') ?: null;
    // }
}
