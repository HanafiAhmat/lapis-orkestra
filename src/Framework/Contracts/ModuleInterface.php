<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Contracts;

/**
 * A Module knows how to register its routes, middleware, interactors, menus and widgets.
 */
interface ModuleInterface
{
    public static function registerHandlers(): void;

    public static function registerRoutes(): void;

    public static function registerUIs(): void;
}
