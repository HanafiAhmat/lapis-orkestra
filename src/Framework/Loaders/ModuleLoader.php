<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Loaders;

use BitSynama\Lapis\Framework\Contracts\ModuleInterface;
use BitSynama\Lapis\Framework\DTO\ModuleDefinition;
use BitSynama\Lapis\Lapis;
use Composer\InstalledVersions;
use RuntimeException;
use function array_key_first;
use function class_exists;
use function file_get_contents;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function json_decode;
use function realpath;
use function rtrim;
use function uasort;
use const DIRECTORY_SEPARATOR;

final class ModuleLoader
{
    /**
     * @var array<string, ModuleDefinition>
     *     An associative array keyed by compositeKey (e.g. "core.SystemMonitor", "vendor.BlogModule", "app.CustomModule").
     *   compositeKey => ModuleDefinition
     */
    private array $modules = [];

    public function __construct()
    {
        $this->discoverCoreModules();
        $this->discoverVendorModules();
        $this->discoverAppModules();

        uasort($this->modules, fn (ModuleDefinition $a, ModuleDefinition $b) => $a->priority <=> $b->priority);

        Lapis::configRegistry()->set('modules', $this->modules);
    }

    /**
     * Return all ModuleDefinition instances in load‐order (compositeKey ⇒ definition).
     *
     * @return array<string, ModuleDefinition>
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Fetch one ModuleDefinition by compositeKey (“core.MyModule” or “vendor.BlogModule” or “app.BlogModule”).
     */
    public function get(string $compositeKey): ModuleDefinition
    {
        return $this->modules[$compositeKey]
            ?? throw new RuntimeException("Module “{$compositeKey}” not found or disabled.");
    }

    /**
     * Instantiate each module’s “Module” class and call registerHandlers().
     */
    public function registerHandlers(): void
    {
        foreach ($this->modules as $module) {
            if (! $module->enabled) {
                continue;
            }

            $moduleClass = $module->namespace . $module->moduleKey . 'Module';
            if (! class_exists($moduleClass)) {
                throw new RuntimeException(
                    "Class {$moduleClass} not found for module “{$module->getCompositeKey()}”."
                );
            }
            /** @var ModuleInterface $instance */
            $instance = new $moduleClass();
            $instance->registerHandlers();
        }
    }

    /**
     * Instantiate each module’s “Module” class and call registerRoutes().
     */
    public function registerRoutes(): void
    {
        foreach ($this->modules as $module) {
            if (! $module->enabled) {
                continue;
            }

            $moduleClass = $module->namespace . $module->moduleKey . 'Module';
            if (! class_exists($moduleClass)) {
                throw new RuntimeException(
                    "Class {$moduleClass} not found for module “{$module->getCompositeKey()}”."
                );
            }
            /** @var ModuleInterface $instance */
            $instance = new $moduleClass();
            $instance->registerRoutes();
        }
    }

    /**
     * Instantiate each module’s “Module” class and call registerUIs().
     */
    public function registerUIs(): void
    {
        foreach ($this->modules as $module) {
            if (! $module->enabled) {
                continue;
            }

            $moduleClass = $module->namespace . $module->moduleKey . 'Module';
            if (! class_exists($moduleClass)) {
                throw new RuntimeException(
                    "Class {$moduleClass} not found for module “{$module->getCompositeKey()}”."
                );
            }
            /** @var ModuleInterface $instance */
            $instance = new $moduleClass();
            $instance->registerUIs();
        }
    }

    private function discoverCoreModules(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir') ?? $ds;
        $modulesFile = implode($ds, [$repoDir, 'src', 'config', 'modules.php']);
        if (! is_file($modulesFile)) {
            return;
        }

        $coreModulesDir = realpath(implode($ds, [$repoDir, 'src', 'Modules']));
        if (! $coreModulesDir || ! is_dir($coreModulesDir)) {
            return;
        }

        /** @var array<string, mixed> $modulesList */
        $modulesList = require $modulesFile;
        $modulesList = is_array($modulesList) ? $modulesList : [];

        foreach ($modulesList as $moduleKey => $moduleInfo) {
            if (! is_string($moduleKey)) {
                continue;
            }

            $modulePath = $coreModulesDir . $ds . $moduleKey;
            if (! is_dir($modulePath)) {
                continue;
            }

            $compositeKey = "core.{$moduleKey}";

            $realModulePath = realpath($modulePath);

            $enabled = true;
            $priority = 10;

            if (is_array($moduleInfo)) {
                $enabled = isset($moduleInfo['enabled']) ? (bool) $moduleInfo['enabled'] : true;
                $priority = isset($moduleInfo['priority']) ? (int) $moduleInfo['priority'] : 10;
            }

            $this->modules[$compositeKey] = new ModuleDefinition(
                source: 'core',
                moduleKey: (string) $moduleKey,
                enabled: $enabled,
                priority: $priority,
                package: null,
                path: is_string($realModulePath) ? $realModulePath : $ds,
                namespace: "BitSynama\\Lapis\\Modules\\{$moduleKey}\\"
            );
        }
    }

    private function discoverVendorModules(): void
    {
        $type = 'bitsynama-lapis-module';
        $lapisPlugins = InstalledVersions::getInstalledPackagesByType($type);
        foreach ($lapisPlugins as $packageName) {
            $installDir = InstalledVersions::getInstallPath($packageName);
            $composerJson = $installDir . DIRECTORY_SEPARATOR . 'composer.json';
            if (! is_file($composerJson)) {
                continue;
            }
            $data = json_decode((string) file_get_contents($composerJson), true);
            if (! is_array($data)) {
                continue;
            }
            if (! isset($data['extra']) || ! is_array($data['extra'])) {
                continue;
            }
            $lapis = $data['extra']['lapis-module'] ?? null;
            if (! is_array($lapis)) {
                continue;
            }

            $moduleKey = $lapis['module-key'] ?? '';
            $moduleRel = $lapis['module-path'] ?? '';
            $priority = (int) ($lapis['priority'] ?? 100);
            $psrMap = $data['autoload']['psr-4'] ?? [];
            $namespace = $psrMap
                ? rtrim((string) array_key_first($psrMap), '\\') . '\\'
                : '';

            if (
                empty($moduleKey) || ! is_string($moduleKey)
                || empty($moduleRel) || ! is_string($moduleRel)
                || empty($namespace)
            ) {
                continue;
            }

            // Compose a unique key: "vendor.<moduleKey>"
            $compositeKey = "vendor.{$moduleKey}";

            // If child config already defined this compositeKey, skip
            if (isset($this->modules[$compositeKey])) {
                continue;
            }

            // Build absolute path to module root:
            $fullPath = realpath($installDir . DIRECTORY_SEPARATOR . $moduleRel);
            if (! $fullPath) {
                continue;
            }

            $this->modules[$compositeKey] = new ModuleDefinition(
                source: 'vendor',
                moduleKey: $moduleKey,
                enabled: true,          // default—child can override in config below
                priority: $priority,
                package: $packageName,
                path: $fullPath,
                namespace: $namespace
            );
        }
    }

    private function discoverAppModules(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir') ?? $ds;
        $appModulesFile = implode($ds, [$projectDir, 'app', 'config', 'modules.php']);
        if (! is_file($appModulesFile)) {
            return;
        }

        $appModulesDir = realpath(implode($ds, [$projectDir, 'app', 'Modules']));

        /** @var array<string, mixed> $modulesList */
        $modulesList = require $appModulesFile;
        $modulesList = is_array($modulesList) ? $modulesList : [];

        foreach ($modulesList as $moduleKey => $moduleInfo) {
            if (! is_string($moduleKey) || ! is_array($moduleInfo)) {
                continue;
            }

            $source = isset($moduleInfo['source']) && is_string($moduleInfo['source'])
                ? $moduleInfo['source']
                : 'app';

            $compositeKey = "{$source}.{$moduleKey}";
            if (isset($this->modules[$compositeKey])) {
                $md = $this->modules[$compositeKey];
                if (isset($moduleInfo['enabled'])) {
                    $md->enabled = (bool) $moduleInfo['enabled'];
                }
                if (isset($moduleInfo['priority'])) {
                    $md->priority = (int) $moduleInfo['priority'];
                }
            } else {
                if (! is_string($appModulesDir) || ! is_dir($appModulesDir)) {
                    return;
                }

                $modulePath = realpath($appModulesDir . $ds . $moduleKey);
                if (! is_string($modulePath) || ! is_dir($modulePath)) {
                    continue;
                }

                $enabled = $moduleInfo['enabled'] ?? true;
                $priority = (int) ($moduleInfo['priority'] ?? 200);
                $namespace = "App\\Modules\\{$moduleKey}\\";

                $this->modules[$compositeKey] = new ModuleDefinition(
                    source: 'app',
                    moduleKey: $moduleKey,
                    enabled: $enabled,
                    priority: $priority,
                    package: null,
                    path: $modulePath,
                    namespace: $namespace
                );
            }
        }
    }
}
