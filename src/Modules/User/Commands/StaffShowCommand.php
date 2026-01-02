<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Commands;

use BitSynama\Lapis\Modules\User\Entities\Staff;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'staff:show', description: 'Show Staff User')]
class StaffShowCommand extends Command
{
    protected function configure()
    {
        $this
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Staff user id')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Staff user email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $id = $input->getOption('id');
        $email = $input->getOption('email');

        if (empty($email) && empty($id)) {
            $io->error('Either ID or Email must be provided.');
            return Command::INVALID;
        }

        if ($id) {
            /** @var Staff $user */
            $user = Staff::find($id);
        } else {
            /** @var Staff $user */
            $user = Staff::where('email', $email)->first();
        }

        if (empty($user)) {
            $io->error('Staff not found.');
            return Command::FAILURE;
        }

        $rows = [
            [
                'id' => $user->staff_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ];

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Email Address', 'Role', 'Status'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}
