<?php declare(strict_types=1);

namespace BitSynama\Lapis;

use BitSynama\Lapis\Framework\DTO\ModuleDefinition;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Framework\Loaders\CommandAutoLoader;
use Symfony\Component\Console\Application;
use function is_array;
use function is_string;
use const DIRECTORY_SEPARATOR;

final class LapisConsole
{
    public static function run(): void
    {
        Lapis::boot();

        $appName = Lapis::configRegistry()->get('app.name');
        $appName = is_string($appName) ? $appName : 'Lapis Orkestra';

        $console = new Application($appName . ' CLI', '0.1.0');

        $modules = Lapis::configRegistry()->get('modules');
        /** @var array<string, ModuleDefinition> $modules */
        $modules = is_array($modules) ? $modules : [];

        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir') ?? DIRECTORY_SEPARATOR;

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir') ?? DIRECTORY_SEPARATOR;

        $dirsToScan = Atlas::findDirectories('Commands', $repoDir, $projectDir, $modules);

        $commands = CommandAutoLoader::discover($dirsToScan);
        foreach ($commands as $cmd) {
            $console->add($cmd);
        }

        $console->run();
    }
}
