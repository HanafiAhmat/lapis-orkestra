<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Commands;

use Phinx\Console\Command\SeedCreate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'seed:create')]
final class PhinxSeedCreateCommand extends SeedCreate
{
    use LapisCommandTrait;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $input = $this->setPhinxConfig($input, $output);

        return parent::execute($input, $output);
    }
}
