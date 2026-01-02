<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Commands;

use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Modules\User\Actions\CustomerStoreAction;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use Carbon\Carbon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'customer:create', description: 'Create customer')]
class CustomerCreateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Customer email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Customer password')
            ->addOption('password_confirm', null, InputOption::VALUE_REQUIRED, 'Password confirm')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Customer name', 'Customer User')
            ->addOption('status', null, InputOption::VALUE_OPTIONAL, 'Customer status', UserStatus::ACTIVE->value);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputs = [
            'email' => $input->getOption('email'),
            'name' => $input->getOption('name'),
            'password' => $input->getOption('password'),
            'password_confirm' => $input->getOption('password_confirm'),
            'status' => $input->getOption('status'),
            'email_verified_at' => Carbon::now()->toDateTimeString(),
        ];

        try {
            $action = new CustomerStoreAction($inputs);
            $user = $action->handle();

            /** @var string $email */
            $email = $inputs['email'];
            $io->success("Customer `{$email}` has been created successfully.");

            return Command::SUCCESS;
        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $errorField => $errorMessage) {
                $io->error("{$errorField}: {$errorMessage}");
            }

            return Command::FAILURE;
        } catch (Throwable) {
            $io->success('Unexpected error');

            return Command::INVALID;
        }
    }
}
