<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Loaders;

use DirectoryIterator;
// use RecursiveDirectoryIterator;
// use RecursiveIteratorIterator;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use function array_values;
use function class_exists;
use function is_dir;
use function rtrim;

final class CommandAutoLoader
{
    /**
     * Discover all Symfony Commands in the given directories.
     *
     * @param string[] $directories  Absolute paths to “Console/Command” folders
     * @return Command[]            Array of instantiated Command objects,
     *                              keyed by their “name” to allow overrides.
     */
    public static function discover(array $directories): array
    {
        $commands = [];
        foreach ($directories as $dir => $namespace) {
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new DirectoryIterator($dir);
            foreach ($iterator as $fileInfo) {
                if (! $fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
                    continue;
                }

                $className = $namespace . rtrim($fileInfo->getFilename(), '.php');
                if (! class_exists($className)) {
                    continue;
                }

                $ref = new ReflectionClass($className);
                if ($ref->isInstantiable() && $ref->isSubclassOf(Command::class)) {
                    /** @var Command $instance */
                    $instance = $ref->newInstance();

                    // Use the command name (getName()) as the key, so later ones override earlier.
                    $name = $instance->getName();
                    if ($name) {
                        $commands[$name] = $instance;
                    }
                }
            }
        }

        // Return instantiated commands in a numerically indexed array
        return array_values($commands);
    }
}
