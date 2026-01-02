<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Commands;

use Phinx\Console\Command\Status;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'migration:status')]
final class PhinxMigrationStatusCommand extends Status
{
    use LapisCommandTrait;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $input = $this->setPhinxConfig($input, $output);

        return parent::execute($input, $output);
    }
}
