<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor;

use BitSynama\Lapis\Framework\Contracts\ModuleInterface;
use BitSynama\Lapis\Lapis;

// use BitSynama\Lapis\Modules\SystemMonitor\Interactors\DummyTalkieInteractor;

final class SystemMonitorModule implements ModuleInterface
{
    public static function registerHandlers(): void
    {
        // Lapis::interactorRegistry()->set(
        //     'coredummy.dummytalkie',
        //     DummyTalkieInteractor::class
        // );
    }

    public static function registerRoutes(): void
    {
        SystemMonitorRoutes::register();
    }

    public static function registerUIs(): void
    {
        SystemMonitorUIs::register();
    }
}
