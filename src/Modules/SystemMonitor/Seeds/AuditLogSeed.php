<?php declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class AuditLogSeed extends AbstractSeed
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
        $table = $this->table('audit_logs');
        $table->truncate();
        $table->insert([
            [
                'actor_type' => 'StaffUser',
                'actor_id' => null,
                'action' => 'did something',
                'metadata' => '{"did":"something"}',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'actor_type' => 'StaffUser',
                'actor_id' => null,
                'action' => 'did another thing',
                'metadata' => '{"did":"another thing"}',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ])->saveData();
    }
}
