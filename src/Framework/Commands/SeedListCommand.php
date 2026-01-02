<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Commands;

use BitSynama\Lapis\Framework\DTO\ModuleDefinition;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use DirectoryIterator;
use Phinx\Seed\AbstractSeed;
use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function is_array;
use function is_dir;
use function rtrim;
use const DIRECTORY_SEPARATOR;

#[AsCommand(name: 'seed:list', description: 'List all available seeders')]
class SeedListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modules = Lapis::configRegistry()->get('modules');
        /** @var array<string, ModuleDefinition> $modules */
        $modules = is_array($modules) ? $modules : [];

        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir') ?? DIRECTORY_SEPARATOR;

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir') ?? DIRECTORY_SEPARATOR;

        $dirsToScan = Atlas::findDirectories('Seeds', $repoDir, $projectDir, $modules);
        // // $seeders = [
        // //     'BlogCategorySeeder',
        // //     'BlogTagSeeder',
        // //     'BlogPostSeeder',
        // //     'BlogCommentSeeder',
        // //     'BlogPageSeeder'
        // // ];

        $seeders = [];
        foreach ($dirsToScan as $dir => $namespace) {
            if (! is_dir($dir)) {
                continue;
            }

            $iterator = new DirectoryIterator($dir);
            foreach ($iterator as $fileInfo) {
                if (! $fileInfo->isFile() || $fileInfo->getExtension() !== 'php') {
                    continue;
                }

                // $className = $namespace . rtrim($fileInfo->getFilename(), '.php');
                $className = rtrim($fileInfo->getFilename(), '.php');
                // if (! class_exists($className)) {
                //     continue;
                // }

                // $ref = new ReflectionClass($className);
                // dump($className, $fileInfo);
                // if ($ref->isInstantiable() && $ref->isSubclassOf(AbstractSeed::class)) {
                $seeders[] = $className;
                // }
            }
        }

        $output->writeln('<info>Available Seeds:</info>');
        foreach ($seeders as $name) {
            $output->writeln(" - {$name}");
        }

        return self::SUCCESS;
    }
}
