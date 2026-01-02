<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Contracts;

/**
 * A Module knows how to register its routes.
 */
interface ModuleRoutesInterface
{
    public static function register(): void;
}
