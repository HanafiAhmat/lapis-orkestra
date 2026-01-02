<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security;

// use BitSynama\Lapis\Modules\Security\Middlewares\AudClaimMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\CorsMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\CsrfMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\DeviceFingerprintMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\EnforceMfaMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\IpAccessMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\LoginThrottleMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\MfaRequiredMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\RateLimitByUserMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\RateLimiterMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\ReferrerPolicyMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\SecurityHeadersMiddleware;
// use BitSynama\Lapis\Modules\Security\Middlewares\SslEnforcerMiddleware;

use BitSynama\Lapis\Framework\Contracts\ModuleInterface;
use BitSynama\Lapis\Lapis;
// use BitSynama\Lapis\Modules\Security\Interactors\DeviceFingerprintInteractor;
use BitSynama\Lapis\Modules\Security\Interactors\JwtTokenInteractor;
// use BitSynama\Lapis\Modules\Security\Interactors\TokenExtractorInteractor;
// use BitSynama\Lapis\Modules\Security\Interactors\MfaInteractor;
// use BitSynama\Lapis\Modules\Security\Interactors\NonceTokenInteractor;
use BitSynama\Lapis\Modules\Security\Interactors\RevokedTokenInteractor;
// use BitSynama\Lapis\Modules\Security\Interactors\SecurityContextInteractor;
use BitSynama\Lapis\Modules\Security\Middlewares\AuthMiddleware;
use BitSynama\Lapis\Modules\Security\Middlewares\CsrfProtectionMiddleware;
use BitSynama\Lapis\Modules\Security\Middlewares\JwtAuthenticationMiddleware;

final class SecurityModule implements ModuleInterface
{
    public static function registerHandlers(): void
    {
        // // Bind core module service into the container
        // Lapis::interactorRegistry()->set('core.security.device_fingerprint', DeviceFingerprintInteractor::class);
        Lapis::interactorRegistry()->set('core.security.jwt_token', JwtTokenInteractor::class);
        // Lapis::interactorRegistry()->set('core.security.mfa', MfaInteractor::class);
        // Lapis::interactorRegistry()->set('core.security.nonce_token', NonceTokenInteractor::class);
        Lapis::interactorRegistry()->set('core.security.revoked_token', RevokedTokenInteractor::class);
        // Lapis::interactorRegistry()->set('core.security.security_context', SecurityContextInteractor::class);
        // Lapis::interactorRegistry()->set('core.security.token_extractor', TokenExtractorInteractor::class);

        Lapis::middlewareRegistry()->set('core.security.auth', AuthMiddleware::class);
        Lapis::middlewareRegistry()->set('core.security.csrf_protection', CsrfProtectionMiddleware::class);
        Lapis::middlewareRegistry()->set('core.security.jwt_authentication', JwtAuthenticationMiddleware::class);

        // Lapis::->middleware()->set('security.aud_claim', AudClaimMiddleware::class);
        // Lapis::->middleware()->set('security.cors', CorsMiddleware::class);
        // Lapis::->middleware()->set('security.device_fingerprint', DeviceFingerprintMiddleware::class);
        // Lapis::->middleware()->set('security.referrer_policy', ReferrerPolicyMiddleware::class);
        // Lapis::->middleware()->set('security.headers', SecurityHeadersMiddleware::class);
        // Lapis::->middleware()->set('security.ssl_enforcer', SslEnforcerMiddleware::class);

        // Lapis::->middleware()->set('security.enforce_mfa', EnforceMfaMiddleware::class);
        // Lapis::->middleware()->set('security.ip_access', IpAccessMiddleware::class);
        // Lapis::->middleware()->set('security.login_throttle', LoginThrottleMiddleware::class);
        // Lapis::->middleware()->set('security.mfa_required', MfaRequiredMiddleware::class);
        // Lapis::->middleware()->set('security.rate_limit_by_user', RateLimitByUserMiddleware::class);
        // Lapis::->middleware()->set('security.rate_limiter', RateLimiterMiddleware::class);
    }

    public static function registerRoutes(): void
    {
        SecurityRoutes::register();
    }

    public static function registerUIs(): void
    {
        SecurityUIs::register();
    }
}
