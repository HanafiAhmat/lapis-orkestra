<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User;

use BitSynama\Lapis\Framework\Contracts\ModuleUIsInterface;
use BitSynama\Lapis\Framework\DTO\MenuItemDefinition;
use BitSynama\Lapis\Framework\DTO\WidgetDefinition;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\User\Entities\Customer;
use BitSynama\Lapis\Modules\User\Entities\Staff;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use Carbon\Carbon;

class UserUIs implements ModuleUIsInterface
{
    public static function register(): void
    {
        self::registerMenus();
        self::registerWidgets();
    }

    protected static function registerMenus(): void
    {
        /** @var string $adminPrefix */
        $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

        Lapis::adminMenuRegistry()->set('main', MenuItemDefinition::fromArray([
            'id' => 'customers',
            'label' => 'Customers',
            'icon' => 'bi-people',
            'href' => $adminPrefix . '/customers',
            'order' => 90,
        ]));

        Lapis::adminMenuRegistry()->set('main', MenuItemDefinition::fromArray([
            'id' => 'staffs',
            'label' => 'Staffs',
            'icon' => 'bi-person-badge',
            'href' => $adminPrefix . '/staffs',
            'order' => 91,
        ]));
    }

    protected static function registerWidgets(): void
    {
        Lapis::adminWidgetRegistry()->set('dashboard', WidgetDefinition::fromArray([
            'id' => 'customer-count',
            'title' => 'Customer Stats',
            'render' => function (): string {
                // Guard: if table missing, show a friendly notice card
                if (! Customer::tableExists()) {
                    return Lapis::viewUtility()->getAdapter()->render(
                        'widgets:admin.shared.setup-required',
                        [
                            'title' => 'Customer Stats',
                            'message' => 'Customer table is not available yet.',
                            'commands' => [
                                'php bin/console migration:migrate',
                                'php bin/console seed:run --class=CustomersSeed',
                            ],
                        ]
                    );
                }

                $stats = [
                    'total_customers' => Customer::count(),
                    'new_this_week' => Customer::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
                ];

                return Lapis::viewUtility()->getAdapter()->render(
                    'widgets:admin.customer.customer-count',
                    [
                        'stats' => $stats,
                    ]
                );
            },
            'order' => 13,
            'colClass' => 'col-md-6 col-lg-3',       // Small widget
            'containerClass' => 'card text-center bg-light', // Minimal style
        ]));

        Lapis::adminWidgetRegistry()->set('dashboard', WidgetDefinition::fromArray([
            'id' => 'staff-overview',
            'title' => 'Staff Overview',
            'render' => function (): string {
                if (! Staff::tableExists()) {
                    return Lapis::viewUtility()->getAdapter()->render(
                        'widgets:admin.shared.setup-required',
                        [
                            'title' => 'Staff Overview',
                            'message' => 'Staff table is not available yet.',
                            'commands' => [
                                'php bin/console migration:migrate',
                                'php bin/console seed:run --class=StaffsSeed',
                            ],
                        ]
                    );
                }

                $active = UserStatus::ACTIVE->value;
                $stats = [
                    'total_staff' => Staff::count(),
                    'active_staff' => Staff::where('status', $active)->count(),
                    'inactive_staff' => Staff::where('status', '!=', $active)->count(),
                ];

                return Lapis::viewUtility()->getAdapter()->render(
                    'widgets:admin.staff.staff-overview',
                    [
                        'stats' => $stats,
                    ]
                );
            },
            'order' => 14,
        ]));
    }
}
