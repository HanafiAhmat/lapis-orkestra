<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security;

use BitSynama\Lapis\Framework\Contracts\ModuleRoutesInterface;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
// use BitSynama\Lapis\Modules\Security\Controllers\ThrottleDebugController;
use BitSynama\Lapis\Framework\DTO\MiddlewareDefinition;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Controllers\AuthController;
use BitSynama\Lapis\Modules\Security\Controllers\MfaController;
// use BitSynama\Lapis\Modules\Security\Middlewares\RateLimiterMiddleware;
use Psr\Http\Message\ServerRequestInterface;

class SecurityRoutes implements ModuleRoutesInterface
{
    public static function register(): void
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $route = Lapis::routeRegistry();

        $route->addGroup('/security', function ($route) {
            $route->add(
                'GET',
                '/csrf-token',
                fn (ServerRequestInterface $request): ActionResponse
                    => new ActionResponse(
                        status: ActionResponse::SUCCESS,
                        data: [
                            'csrf_token' => Lapis::sessionUtility()->getCsrfToken(),
                        ],
                        message: 'CSRF token issued'
                    ),
                [new MiddlewareDefinition('core.security.ratelimiter')]
            );

            // $route->addGroup('/mfa', function ($route) {
            //     $route->post('/issue', [MfaController::class, 'issue']);
            //     $route->post('/verify', [MfaController::class, 'verify']);
            //     $route->post('/revoke-trusted', [MfaController::class, 'revoke']);

            //     $route->addGroup('/totp', function ($route) {
            //         $route->get('/setup', [MfaController::class, 'totpSetup']);
            //         $route->delete('/reset', [MfaController::class, 'totpReset']);
            //     });
            // });
        });

        $route->addGroup('/auth', function ($route) {
            $route->add('GET', '/login', [AuthController::class, 'login']);
            $route->add('GET', '/register', [AuthController::class, 'register']);
            $route->add('GET', '/email-verification', [AuthController::class, 'emailVerification']);
            $route->add('GET', '/password-reset-request', [AuthController::class, 'passwordResetRequest']);
            $route->add('PATCH', '/refresh-token', [AuthController::class, 'refreshToken']);
            $route->add('GET', '/password-reset-confirmation', [AuthController::class, 'passwordResetConfirmation']);

            // should be a post, put or patch request?
            $route->add('GET', '/resend-email-verification', [AuthController::class, 'resendEmailVerification']);

            // $route->addGroup('', function ($route) {
            $route->add('POST', '/register', [AuthController::class, 'register']);
            $route->add('POST', '/login', [AuthController::class, 'login']);
            $route->add('POST', '/logout', [AuthController::class, 'logout']);
            $route->add('POST', '/password-reset-request', [AuthController::class, 'passwordResetRequest']);
            $route->add(
                'POST',
                '/password-reset-confirmation',
                [AuthController::class, 'passwordResetConfirmation']
            );
            // }, [new MiddlewareDefinition('core.security.ratelimiter')]);
        });

        $route->addGroup($adminPrefix . '/auth', function ($route) {
            $route->add('GET', '/login', [AuthController::class, 'login']);
            $route->add('GET', '/email-verification', [AuthController::class, 'emailVerification']);
            $route->add('GET', '/password-reset-request', [AuthController::class, 'passwordResetRequest']);
            $route->add('PATCH', '/refresh-token', [AuthController::class, 'refreshToken']);
            $route->add('GET', '/password-reset-confirmation', [AuthController::class, 'passwordResetConfirmation']);

            // should be a post, put or patch request?
            $route->add('GET', '/resend-email-verification', [AuthController::class, 'resendEmailVerification']);

            // $route->addGroup('', function ($route) {
            $route->add('POST', '/login', [AuthController::class, 'login']);
            $route->add('POST', '/logout', [AuthController::class, 'logout']);
            $route->add('POST', '/password-reset-request', [AuthController::class, 'passwordResetRequest']);
            $route->add(
                'POST',
                '/password-reset-confirmation',
                [AuthController::class, 'passwordResetConfirmation']
            );
            // }, [new MiddlewareDefinition('core.security.ratelimiter')]);
        });
    }
}
