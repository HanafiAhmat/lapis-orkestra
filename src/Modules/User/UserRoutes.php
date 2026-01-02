<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User;

use BitSynama\Lapis\Framework\Contracts\ModuleRoutesInterface;
// use BitSynama\Lapis\Modules\Security\Controllers\ThrottleDebugController;
use BitSynama\Lapis\Framework\DTO\MiddlewareDefinition;
use BitSynama\Lapis\Framework\Registries\RouteRegistry;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Controllers\Admin\CustomerController as AdminCustomerController;
use BitSynama\Lapis\Modules\User\Controllers\Admin\StaffController as AdminStaffController;

// use BitSynama\Lapis\Modules\Security\Middlewares\RateLimiterMiddleware;

class UserRoutes implements ModuleRoutesInterface
{
    public static function register(): void
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $route = Lapis::routeRegistry();

        $route->addGroup($adminPrefix, function (RouteRegistry $route) {
            $route->addGroup('/staffs', function (RouteRegistry $route) {
                $route->add('GET', '', (new AdminStaffController())->list(...));
                $route->add('GET', '/{id:\d+}', (new AdminStaffController())->show(...));
                $route->add('GET', '/create', (new AdminStaffController())->create(...));
                $route->add('POST', '', (new AdminStaffController())->store(...));
                $route->add('GET', '/{id:\d+}/edit', (new AdminStaffController())->edit(...));
                $route->add('PUT', '/{id:\d+}', (new AdminStaffController())->update(...));
                $route->add('DELETE', '/{id:\d+}', (new AdminStaffController())->destroy(...));
            }, [
                new MiddlewareDefinition('core.security.auth'),
                // MiddlewareRegistry::get('staffuser', 'role', ['superuser', 'manager'])
            ]);

            $route->addGroup('/customers', function (RouteRegistry $route) {
                $route->add('GET', '', (new AdminCustomerController())->list(...));
                $route->add('GET', '/{id:\d+}', (new AdminCustomerController())->show(...));
                $route->add('GET', '/create', (new AdminCustomerController())->create(...));
                $route->add('POST', '', (new AdminCustomerController())->store(...));
                $route->add('GET', '/{id:\d+}/edit', (new AdminCustomerController())->edit(...));
                $route->add('PUT', '/{id:\d+}', (new AdminCustomerController())->update(...));
                $route->add('DELETE', '/{id:\d+}', (new AdminCustomerController())->destroy(...));
            }, [
                new MiddlewareDefinition('core.security.auth'),
                // MiddlewareRegistry::get('staffuser', 'role', ['superuser', 'manager'])
            ]);
        });
    }
}
