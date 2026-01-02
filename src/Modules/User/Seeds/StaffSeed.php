<?php declare(strict_types=1);

use BitSynama\Lapis\Modules\User\Enums\StaffRole;
use BitSynama\Lapis\Modules\User\Enums\UserStatus;
use Faker\Factory;
use Phinx\Seed\AbstractSeed;

class StaffSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $table = $this->table('staffs');
        $table->truncate();
        $faker = Factory::create();

        $data = [
            [
                'name' => 'Staff Superuser',
                'email' => 'admin@local.dev',
                'password' => password_hash('Pass123Word', PASSWORD_BCRYPT),
                'role' => StaffRole::SUPERUSER->value,
                'status' => UserStatus::ACTIVE->value,
            ],
        ];
        for ($i = 0; $i < 20; $i++) {
            $data[] = [
                'name' => $faker->name(),
                'email' => $faker->unique()
                    ->email(),
                'password' => password_hash('Pass123', PASSWORD_BCRYPT),
                'role' => StaffRole::cases()[random_int(0, count(StaffRole::cases()) - 1)]->value,
                'status' => UserStatus::ACTIVE->value,
            ];
        }
        $table->insert($data)
            ->saveData();
    }
}
