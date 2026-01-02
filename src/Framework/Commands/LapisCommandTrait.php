<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Commands;

use BitSynama\Lapis\Lapis;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function is_string;
use function realpath;
use const DIRECTORY_SEPARATOR;

trait LapisCommandTrait
{
    protected function setPhinxConfig(InputInterface $input, OutputInterface $output): InputInterface
    {
        $repoDirVal = Lapis::varRegistry()->get('repo_dir');
        $projectDirVal = Lapis::varRegistry()->get('project_dir');

        // Guard: must be strings at runtime
        if (! is_string($repoDirVal) || ! is_string($projectDirVal)) {
            throw new RuntimeException('`repo_dir` and `project_dir` registry entries must be strings');
        }

        // Now PHPStan knows these are strings
        $repoDirConfig = $repoDirVal . DIRECTORY_SEPARATOR . 'phinx.php';
        $projectDirConfig = $projectDirVal . DIRECTORY_SEPARATOR . 'phinx.php';

        // Prefer project‐level config
        $phinxConfigFile = realpath($projectDirConfig) ?: realpath($repoDirConfig);

        if (! is_string($phinxConfigFile)) {
            $output->writeln('<fg=black;bg=red>Unable to locate phinx config file.</>');
        } else {
            $input->setOption('configuration', $phinxConfigFile);
        }

        return $input;
    }
}
