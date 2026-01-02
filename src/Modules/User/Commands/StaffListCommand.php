<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Commands;

use BitSynama\Lapis\Modules\User\Entities\Staff;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'staff:list', description: 'List Staff Users')]
class StaffListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Staff[] $users */
        $users = Staff::get();

        $rows = [];
        /** @var Staff $user */
        foreach ($users as $user) {
            // if (empty($user)) {
            //     continue;
            // }

            $rows[] = [
                'id' => $user->staff_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ];
        }

        if (empty($rows)) {
            $io->warning('No staff users found.');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Email Address', 'Role', 'Status'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}
