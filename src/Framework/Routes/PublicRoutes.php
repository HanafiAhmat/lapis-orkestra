<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Routes;

use BitSynama\Lapis\Framework\Contracts\ModuleRoutesInterface;
use BitSynama\Lapis\Framework\Controllers\PublicController;
use BitSynama\Lapis\Lapis;
use function class_exists;

class PublicRoutes implements ModuleRoutesInterface
{
    public static function register(): void
    {
        $route = Lapis::routeRegistry();

        // Prefer App override if available
        $publicControllerClass = class_exists(\App\Controllers\PublicController::class)
            ? \App\Controllers\PublicController::class
            : PublicController::class;

        $route->add(method: 'GET', path: '', handler: [$publicControllerClass, 'index']);
    }
}
