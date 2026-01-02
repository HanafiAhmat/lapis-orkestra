<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Commands;

use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Modules\SystemMonitor\Entities\AuditLog;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function in_array;

#[AsCommand(name: 'audit:recent', description: 'View recent audit logs (last 20 entries)')]
class AuditLogRecentCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = Lapis::configRegistry();
        if (in_array($config->get('app.env'), ['production'], true)) {
            $output->writeln('<fg=black;bg=red>Seeders are not available in Production environment.</>');

            return Command::INVALID;
        }

        $io = new SymfonyStyle($input, $output);
        $logs = AuditLog::orderByDesc('created_at')
            ->limit(20)
            ->get();

        if ($logs->count() === 0) {
            $io->warning('No audit logs found.');
            return Command::SUCCESS;
        }

        $table = [];
        /** @var AuditLog|null $log */
        foreach ($logs as $log) {
            if (! $log) {
                continue;
            }

            $table[] = ['', $log->actor_type . ':' . $log->actor_id, $log->action, $log->ip_address];
        }

        $io->table(['Time', 'Actor', 'Action', 'IP'], $table);
        return Command::SUCCESS;
    }
}
