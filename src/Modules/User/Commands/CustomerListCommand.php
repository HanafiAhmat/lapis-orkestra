<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Commands;

use BitSynama\Lapis\Framework\Persistences\AbstractEntity;
use BitSynama\Lapis\Modules\User\Entities\Customer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'customer:list', description: 'List customers')]
class CustomerListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Customer[] $users */
        $users = Customer::get();

        $rows = [];
        /** @var Customer $user */
        foreach ($users as $user) {
            if (! ($user instanceof AbstractEntity)) {
                continue;
            }

            $rows[] = [
                'id' => $user->customer_id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ];
        }

        if (empty($rows)) {
            $io->warning('No customers found.');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Email Address', 'Status'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}
