<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO;

use InvalidArgumentException;
use function in_array;

/**
 * Describes exactly one module—where it comes from, whether it’s enabled,
 * how to load it, and what its PHP namespace is.
 *
 * @property string      $source       One of "core", "vendor", or "app"
 * @property string      $moduleKey    The module’s short key (e.g. "SystemMonitor", "BlogModule")
 * @property bool        $enabled      Can be toggled at runtime
 * @property int         $priority     Load order (lower = load earlier)
 * @property string|null $package      Composer package name (if source="vendor")
 * @property string      $path         Absolute filesystem path to the module root
 * @property string      $namespace    PHP namespace prefix (e.g. "BitSynama\\Lapis\\Modules\\SystemMonitor\\")
 */
class ModuleDefinition
{
    public function __construct(
        public string $source,
        public string $moduleKey,
        public bool $enabled,
        public int $priority,
        public string|null $package,
        public string $path,
        public string $namespace
    ) {
        if (! in_array($this->source, ['core', 'vendor', 'app'], true)) {
            throw new InvalidArgumentException("Invalid module source “{$this->source}”");
        }
    }

    /**
     * “core.SystemMonitor” or “vendor.BlogModule” or “app.UserModule”
     */
    public function getCompositeKey(): string
    {
        return $this->source . '.' . $this->moduleKey;
    }
}
