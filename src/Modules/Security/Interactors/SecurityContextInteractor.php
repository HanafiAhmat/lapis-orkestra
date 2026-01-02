<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Interactors;

use BitSynama\Lapis\Framework\DTO\ModuleDefinition;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\DTO\JwtPayloadDefinition;
use BitSynama\Lapis\Modules\Security\Enums\AuthState;
use BitSynama\Lapis\Modules\Security\Factories\JwtPayloadFactory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

class SecurityContextInteractor
{
    private static bool $resolved = false;

    public static function getAuthStatus(): AuthState
    {
        if (! self::isSecurityModuleEnabled()) {
            return AuthState::Disabled;
        }

        return Lapis::varRegistry()->has('jwt_payload') ? AuthState::Authenticated : AuthState::Unauthenticated;
    }

    public static function getUser(): JwtPayloadDefinition|null
    {
        if (! self::isSecurityModuleEnabled()) {
            return null;
        }

        if (self::$resolved) {
            if (Lapis::varRegistry()->has('jwt_payload')) {
                /** @var JwtPayloadDefinition $jwtPayload */
                $jwtPayload = Lapis::varRegistry()->get('jwt_payload');

                return $jwtPayload;
            }

            return null;
        }

        $token = TokenExtractorInteractor::getAccessToken();
        if (! $token) {
            return null;
        }

        /** @var string $key */
        $key = Lapis::configRegistry()->get('app.encryption_key');
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        try {
            $jwtPayload = JwtPayloadFactory::fromPayload($decoded);
        } catch (InvalidArgumentException) {
            return null;
        }

        // Inject into runtime context
        Lapis::varRegistry()->set('user', $jwtPayload);
        self::$resolved = true;

        return $jwtPayload;
    }

    public static function isAuthenticated(): bool
    {
        return self::getAuthStatus() === AuthState::Authenticated;
    }

    public static function isSecurityModuleEnabled(): bool
    {
        /** @var ModuleDefinition $securityModule */
        $securityModule = Lapis::configRegistry()->get('modules.Security');

        return $securityModule->enabled;
    }

    public static function getClientType(): string
    {
        return Lapis::requestUtility()->getClientType();
    }
}
