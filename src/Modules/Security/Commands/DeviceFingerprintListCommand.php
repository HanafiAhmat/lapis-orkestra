<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Commands;

use BitSynama\Lapis\Modules\Security\Entities\DeviceFingerprint;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'fingerprint:list', description: 'List device fingerprints by user type and user ID')]
class DeviceFingerprintListCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('user-id', null, InputOption::VALUE_REQUIRED, 'User ID')
            ->addOption('user-type', null, InputOption::VALUE_OPTIONAL, 'User type (e.g., staff, customer)', 'staff');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getOption('user-id');
        $userType = $input->getOption('user-type');

        if (empty($userId) || empty($userType)) {
            $io->error('You must provide --user-id');
            return self::FAILURE;
        }

        $fingerprints = DeviceFingerprint::where('user_id', $userId)
            ->where('user_type', $userType)
        ;

        if ($fingerprints->count() === 0) {
            $io->note('No fingerprints found for this user.');
            return self::SUCCESS;
        }

        $rows = [];
        /** @var DeviceFingerprint $entry */
        foreach ($fingerprints->get() as $entry) {
            $rows[] = [
                $entry->device_fingerprint_id,
                $entry->fingerprint,
                $entry->user_agent,
                $entry->ip_address,
                $entry->last_seen_at,
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Fingerprint', 'User Agent', 'IP Address', 'Last Seen'])
            ->setRows($rows)
            ->render();

        return self::SUCCESS;
    }
}
