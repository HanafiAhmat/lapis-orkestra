<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\User\Commands;

use BitSynama\Lapis\Framework\Exceptions\ValidationException;
use BitSynama\Lapis\Modules\User\Actions\StaffStoreAction;
use BitSynama\Lapis\Modules\User\Enums\StaffRole;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use Carbon\Carbon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'staff:create', description: 'Create Staff User')]
class StaffCreateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'Staff user email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Staff user password')
            ->addOption('password_confirm', null, InputOption::VALUE_REQUIRED, 'Password confirm')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Staff user name', 'Staff User')
            ->addOption('role', null, InputOption::VALUE_OPTIONAL, 'Staff user role', StaffRole::MEMBER->value)
            ->addOption(
                'status',
                null,
                InputOption::VALUE_OPTIONAL,
                'Staff user status',
                UserStatus::ACTIVE->value
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputs = [
            'email' => $input->getOption('email'),
            'name' => $input->getOption('name'),
            'password' => $input->getOption('password'),
            'password_confirm' => $input->getOption('password_confirm'),
            'role' => $input->getOption('role'),
            'status' => $input->getOption('status'),
            'email_verified_at' => Carbon::now()->toDateTimeString(),
        ];

        try {
            $action = new StaffStoreAction($inputs);
            $user = $action->handle();

            /** @var string $email */
            $email = $inputs['email'];
            $io->success("Staff user `{$email}` has been created successfully.");

            return Command::SUCCESS;
        } catch (ValidationException $e) {
            foreach ($e->getErrors() as $errorField => $errorMessage) {
                $io->error("{$errorField}: {$errorMessage}");
            }

            return Command::FAILURE;
        } catch (Throwable $e) {
            $io->error('Unexpected error:' . $e->getMessage());

            return Command::INVALID;
        }
    }
}
