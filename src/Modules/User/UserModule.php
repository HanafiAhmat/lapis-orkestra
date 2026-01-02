<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User;

// use BitSynama\Lapis\Modules\User\Middlewares\UserRoleMiddleware;
// use BitSynama\Lapis\Modules\User\Services\UserUserPermissionService;

use BitSynama\Lapis\Framework\Contracts\ModuleInterface;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Middlewares\UserTypeAvailabilityMiddleware;
use BitSynama\Lapis\Modules\User\UserTypes\CustomerUserType;
use BitSynama\Lapis\Modules\User\UserTypes\StaffUserType;

final class UserModule implements ModuleInterface
{
    public static function registerHandlers(): void
    {
        Lapis::userTypeRegistry()->set('staff', StaffUserType::class);
        Lapis::userTypeRegistry()->set('customer', CustomerUserType::class);
        // // later, vendor plugins can do:
        // Lapis::userTypeRegistry()->set('vendor', Acme\VendorPlugin\VendorUser::class);

        // // Bind core module service into the container
        // ServiceRegistry::set('staffuser', 'permission', UserUserPermissionService::class);

        // MiddlewareRegistry::set('staffuser', 'role', UserRoleMiddleware::class);
        Lapis::middlewareRegistry()->set('core.user.user_type_availability', UserTypeAvailabilityMiddleware::class);
    }

    public static function registerRoutes(): void
    {
        UserRoutes::register();
    }

    public static function registerUIs(): void
    {
        UserUIs::register();
    }
}
