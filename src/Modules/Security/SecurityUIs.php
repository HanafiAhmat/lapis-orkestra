<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security;

use BitSynama\Lapis\Framework\Contracts\ModuleUIsInterface;
use BitSynama\Lapis\Framework\DTO\MenuItemDefinition;
use BitSynama\Lapis\Framework\DTO\WidgetDefinition;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\Security\Repositories\SecurityStatsRepository;

class SecurityUIs implements ModuleUIsInterface
{
    public static function register(): void
    {
        self::registerMenus();
        self::registerWidgets();
    }

    protected static function registerMenus(): void
    {
        // $adminPrefix = Lapis::configRegistry()->get('app.routes.admin_prefix');

        // Lapis::adminMenuRegistry()->set('main', MenuItemDefinition::fromArray([
        //     'id'    => 'system',
        //     'label' => 'System Monitor',
        //     'icon'  => 'bi-brightness-low',
        //     'href'  => $adminPrefix . '/monitor',
        //     'order' => 100,
        //     'children' => [
        //         [
        //             'id'    => 'health-check',
        //             'label' => 'Health Check',
        //             'icon'  => 'bi-file-medical',
        //             'href'  => $adminPrefix . '/monitor/health-check',
        //             'order' => 10,
        //         ],
        //         [
        //             'id'    => 'routes',
        //             'label' => 'Routes',
        //             'icon'  => 'bi-router',
        //             'href'  => $adminPrefix . '/monitor/routes',
        //             'order' => 11,
        //         ],
        //         [
        //             'id'    => 'psr-summary',
        //             'label' => 'PSR Summary',
        //             'href'  => $adminPrefix . '/monitor/summary/psr',
        //             'order' => 12,
        //         ],
        //         [
        //             'id'    => 'recent-audit-logs',
        //             'label' => 'Recent Audit Logs',
        //             'href'  => $adminPrefix . '/monitor/audit-logs/recent',
        //             'order' => 13,
        //         ],
        //     ]
        // ]));

        // Lapis::adminMenuRegistry()->set('main', MenuItemDefinition::fromArray([
        //     'id'    => 'test-mailer',
        //     'label' => 'Test Mailer',
        //     'href'  => $adminPrefix . '/monitor/test/mailer',
        //     'order' => 14,
        // ]), 'system');
    }

    protected static function registerWidgets(): void
    {
        Lapis::adminWidgetRegistry()->set('dashboard', WidgetDefinition::fromArray([
            'id' => 'security-stats',
            'title' => 'Security Stats',
            'render' => function (): string {
                $stats = SecurityStatsRepository::securityStats();
                if (! $stats) {
                    return Lapis::viewUtility()->getAdapter()->render(
                        'widgets:admin.shared.setup-required',
                        [
                            'title' => 'Security Overview',
                            'message' => 'Related Security tables are not available yet.',
                            'commands' => ['php bin/console migration:migrate'],
                        ]
                    );
                }

                return Lapis::viewUtility()->getAdapter()->render(
                    'widgets:admin.security-stats',
                    [
                        'stats' => $stats,
                    ]
                );
            },
            'order' => 11,
            'colClass' => 'col-md-12 col-xl-6',      // Larger width for security widget
            'containerClass' => 'card border-primary shadow-lg rounded-3', // Custom style
        ]));

        Lapis::adminWidgetRegistry()->set('dashboard', WidgetDefinition::fromArray([
            'id' => 'auth-session-stats',
            'title' => 'Auth Sessions',
            'render' => function (): string {
                $stats = SecurityStatsRepository::authSessionStats();
                if (! $stats) {
                    return Lapis::viewUtility()->getAdapter()->render(
                        'widgets:admin.shared.setup-required',
                        [
                            'title' => 'Security Authentication Overview',
                            'message' => 'Related Security Authentication tables are not available yet.',
                            'commands' => ['php bin/console migration:migrate'],
                        ]
                    );
                }

                return Lapis::viewUtility()->getAdapter()->render(
                    'widgets:admin.auth-session-stats',
                    [
                        'stats' => $stats,
                    ]
                );
            },
            'order' => 12,
        ]));
    }
}
