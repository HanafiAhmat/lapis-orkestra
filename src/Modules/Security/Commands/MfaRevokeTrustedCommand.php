<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Commands;

use BitSynama\Lapis\Modules\Security\Entities\MfaSecret;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mfa:revoke-trusted', description: 'Revoke trusted MFA devices for a user')]
class MfaRevokeTrustedCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'User ID')
            ->addOption('user-type', null, InputOption::VALUE_REQUIRED, 'User type (e.g., Staff, Customer)')
            ->addOption('fingerprint', null, InputOption::VALUE_OPTIONAL, 'Optional fingerprint to delete only one');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getOption('user-id');
        $userType = $input->getOption('user-type');
        $fingerprint = $input->getOption('fingerprint');

        if (! $userId || ! $userType) {
            $io->error('Missing required --user-id or --user-type');
            return Command::FAILURE;
        }

        $query = MfaSecret::where('user_id', $userId)
            ->where('user_type', $userType)
            ->where('type', 'trusted');

        if ($fingerprint) {
            $query = $query->where('device_fingerprint', $fingerprint);
        }

        $recordCount = $query->count();
        if ($recordCount === 0) {
            $io->warning('No trusted devices found.');

            return Command::SUCCESS;
        }

        $records = $query->get();
        foreach ($records as $record) {
            $record->delete();
        }

        $io->success($recordCount . ' trusted device(s) revoked.');

        return Command::SUCCESS;
    }
}
