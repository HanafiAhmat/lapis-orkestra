<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor;

use BitSynama\Lapis\Framework\Contracts\ModuleUIsInterface;
use BitSynama\Lapis\Framework\DTO\MenuItemDefinition;
use BitSynama\Lapis\Framework\DTO\WidgetDefinition;
use BitSynama\Lapis\Framework\Foundation\Runtime;
use BitSynama\Lapis\Lapis;
use const PHP_VERSION;

class SystemMonitorUIs implements ModuleUIsInterface
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
            'id' => 'system',
            'label' => 'System Monitor',
            'icon' => 'bi-brightness-low',
            'href' => $adminPrefix . '/monitor',
            'order' => 100,
            'children' => [
                [
                    'id' => 'health-check',
                    'label' => 'Health Check',
                    'icon' => 'bi-file-medical',
                    'href' => $adminPrefix . '/monitor/health-check',
                    'order' => 10,
                ],
                [
                    'id' => 'routes',
                    'label' => 'Routes',
                    'icon' => 'bi-router',
                    'href' => $adminPrefix . '/monitor/routes',
                    'order' => 11,
                ],
                [
                    'id' => 'psr-summary',
                    'label' => 'PSR Summary',
                    'href' => $adminPrefix . '/monitor/summary/psr',
                    'order' => 12,
                ],
                [
                    'id' => 'recent-audit-logs',
                    'label' => 'Recent Audit Logs',
                    'href' => $adminPrefix . '/monitor/audit-logs/recent',
                    'order' => 13,
                ],
            ],
        ]));

        Lapis::adminMenuRegistry()->set('main', MenuItemDefinition::fromArray([
            'id' => 'test-mailer',
            'label' => 'Test Mailer',
            'href' => $adminPrefix . '/monitor/test/mailer',
            'order' => 14,
        ]), 'system');
    }

    protected static function registerWidgets(): void
    {
        $stats = [
            'uptime' => Runtime::uptime() ?? 'Unknown',
            'loadAvg' => Runtime::loadAverage() ?? 'Unknown',
            'phpVersion' => PHP_VERSION,
            'memoryUsage' => Runtime::memoryUsage() ?? [
                'used' => 'Unknown',
                'total' => 'Unknown',
            ], // e.g., ['used' => '1.2 GB', 'total' => '4 GB']
        ];
        Lapis::adminWidgetRegistry()->set('dashboard', WidgetDefinition::fromArray([
            'id' => 'system-health',
            'title' => 'System Health',
            'render' => fn () => Lapis::viewUtility()->getAdapter()->render('widgets:system_monitor.system-health', [
                'stats' => $stats,
            ]),
            'order' => 10,
        ]));
    }
}
