<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Commands;

use BitSynama\Lapis\Modules\Security\Entities\DeviceFingerprint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'fingerprint:delete', description: 'Delete a specific device fingerprint by ID or fingerprint hash')]
class DeviceFingerprintDeleteCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Device fingerprint ID')
            ->addOption('fingerprint', null, InputOption::VALUE_OPTIONAL, 'Device fingerprint hash');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var int|null $id */
        $id = $input->getOption('id');
        $fingerprint = $input->getOption('fingerprint');

        if (empty($id) && empty($fingerprint)) {
            $io->error('You must provide either --id or --fingerprint to delete.');
            return self::FAILURE;
        }

        if ($id) {
            /** @var DeviceFingerprint|null $record */
            $record = DeviceFingerprint::find($id);
        } else {
            /** @var DeviceFingerprint|null $record */
            $record = DeviceFingerprint::where('fingerprint', $fingerprint)->first();
        }

        if (empty($record)) {
            $io->warning('Device fingerprint not found.');
            return self::SUCCESS;
        }

        $record->delete();
        $io->success('Device fingerprint deleted successfully.');

        return self::SUCCESS;
    }
}
