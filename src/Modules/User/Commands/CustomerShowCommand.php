<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Commands;

use BitSynama\Lapis\Modules\User\Entities\Customer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'customer:show', description: 'Show customer')]
class CustomerShowCommand extends Command
{
    protected function configure()
    {
        $this
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Customer id')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Customer email');
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
            /** @var Customer $user */
            $user = Customer::find($id);
        } else {
            /** @var Customer $user */
            $user = Customer::where('email', $email)->first();
        }

        if (empty($user)) {
            $io->error('Customer not found.');
            return Command::INVALID;
        }

        $rows = [
            [
                'id' => $user->customer_id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ];

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Email Address', 'Status'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}
