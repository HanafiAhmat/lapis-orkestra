<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Commands;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'seed:clear-one', description: 'Clear by table name')]
class SeedClearByNameCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('table', InputArgument::REQUIRED, 'Table name to clear');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $table */
        $table = $input->getArgument('table');

        try {
            DB::table($table)->truncate();
            $output->writeln("<info>Table '{$table}' cleared successfully.</info>");
        } catch (Exception $e) {
            $output->writeln("<error>Failed to clear table '{$table}': {$e->getMessage()}</error>");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
