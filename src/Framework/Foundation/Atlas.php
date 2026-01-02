<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use BitSynama\Lapis\Framework\DTO\ModuleDefinition;
use BitSynama\Lapis\Services\ProviderInfo;
use BitSynama\Lapis\Utilities\AdapterInfo;
use DirectoryIterator;
use ReflectionClass;
use function class_exists;
use function is_dir;
use function rtrim;
use function str_ends_with;
use function str_replace;
use function strtolower;
use const DIRECTORY_SEPARATOR;

final class Atlas
{
    /**
     * @param array<string, ModuleDefinition> $modules
     */
    public static function discover(
        string $dirPath,
        string $interface,
        string $attribute,
        string $classSuffix,
        string $type,
        string $key,
        string $repoDir,
        string $projectDir,
        array $modules = []
    ): string {
        $directories = self::findDirectories($dirPath, $repoDir, $projectDir, $modules);

        foreach ($directories as $dir => $namespace) {
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new DirectoryIterator($dir);
            foreach ($iterator as $fileInfo) {
                if (
                    ! $fileInfo->isFile()
                    || $fileInfo->getExtension() !== 'php'
                    || ! str_ends_with($fileInfo->getFilename(), $classSuffix . '.php')
                ) {
                    continue;
                }

                $fqcn = $namespace . rtrim($fileInfo->getFilename(), '.php');
                if (! class_exists($fqcn)) {
                    continue;
                }

                $ref = new ReflectionClass($fqcn);
                if (! $ref->implementsInterface($interface)) {
                    continue;
                }

                foreach ($ref->getAttributes($attribute) as $attr) {
                    /** @var AdapterInfo|ProviderInfo $inst */
                    $inst = $attr->newInstance();
                    if (
                        $inst->type === strtolower($type)
                        && $inst->key === $key
                    ) {
                        return $fqcn;
                    }
                }
            }
        }

        return '';
    }

    /**
     * @param array<string, ModuleDefinition> $modules
     * @return array<string, string>
     */
    public static function findDirectories(
        string $dirPath,
        string $repoDir,
        string $projectDir,
        array $modules = []
    ): array {
        $ds = DIRECTORY_SEPARATOR;

        $dirNamespace = rtrim(str_replace('.', '\\', $dirPath), '\\') . '\\';
        $dirPath = rtrim(str_replace('.', $ds, $dirPath), $ds) . $ds;

        $dirsToScan = [];

        // Lapis Services or Utilities
        $dir = "{$repoDir}{$ds}src{$ds}{$dirPath}";
        if (is_dir($dir)) {
            $dirsToScan[$dir] = 'BitSynama\\Lapis\\' . $dirNamespace;
        }

        // Lapis Framework
        $dir = "{$repoDir}{$ds}src{$ds}Framework{$ds}{$dirPath}";
        if (is_dir($dir)) {
            $dirsToScan[$dir] = 'BitSynama\\Lapis\\Framework\\' . $dirNamespace;
        }

        // Lapis Modules, Vendor Modules, & App Modules
        foreach ($modules as $compositeKey => $moduleDefinition) {
            if (! $moduleDefinition->enabled) {
                continue;
            }

            $modDir = $moduleDefinition->path . $ds . $dirPath;
            if (is_dir($modDir)) {
                $dirsToScan[$modDir] = $moduleDefinition->namespace . $dirNamespace;
            }
        }

        // // App‐level overrides (child application may create its own commands here to override core or vendor modules)
        // $dir = "{$projectDir}{$ds}app{$ds}Framework{$ds}{$dirPath}";
        // if (is_dir($dir)) {
        //     $dirsToScan[$dir] = 'App\\Framework\\' . $dirNamespace;
        // }

        // // App‐level commands here
        $dir = "{$projectDir}{$ds}app{$ds}{$dirPath}";
        if (is_dir($dir)) {
            $dirsToScan[$dir] = 'App\\' . $dirNamespace;
        }

        return $dirsToScan;
    }
}
