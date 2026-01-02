<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\Security\Commands;

use BitSynama\Lapis\Modules\Security\Entities\DeviceFingerprint;
use Carbon\Carbon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'fingerprint:purge', description: 'Delete old fingerprints older than N days')]
class DeviceFingerprintPurgeCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('days', null, InputOption::VALUE_OPTIONAL, 'Purge devices not seen in this many days', 180);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var int $days */
        $days = $input->getOption('days');
        $cutoff = Carbon::now()->subDays($days)->toDateTimeString();

        /** @var string $count */
        $count = DeviceFingerprint::where('last_seen_at', '<', $cutoff)->delete();

        $io->success("Deleted {$count} old fingerprints not seen in the last {$days} days.");
        return self::SUCCESS;
    }
}
