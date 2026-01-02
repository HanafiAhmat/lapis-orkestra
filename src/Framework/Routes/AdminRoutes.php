<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Routes;

use BitSynama\Lapis\Framework\Contracts\ModuleRoutesInterface;
use BitSynama\Lapis\Framework\Controllers\AdminController;
use BitSynama\Lapis\Framework\DTO\MiddlewareDefinition;
use BitSynama\Lapis\Lapis;
use function class_exists;

class AdminRoutes implements ModuleRoutesInterface
{
    public static function register(): void
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');
        $route = Lapis::routeRegistry();

        // Prefer App override if available
        $adminControllerClass = class_exists(\App\Controllers\AdminController::class)
            ? \App\Controllers\AdminController::class
            : AdminController::class;

        $route->addGroup($adminPrefix, function ($route) use ($adminControllerClass) {
            $route->add(method: 'GET', path: '', handler: [$adminControllerClass, 'index']);

            // $route->add(
            //     'GET',
            //     '/stats',
            //     [$adminControllerClass, 'stats'],
            // );
        }, [
            new MiddlewareDefinition('core.security.auth', [
                'allowedTypes' => ['staff'],
            ]),
        ]);
    }
}
