<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAuditLogsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('audit_logs', [
            'id' => 'audit_log_id',
            'comment' => 'Polymorphic Audit Logs',
        ]);

        $table->addColumn('actor_type', 'string', [
            'limit' => 50,
            'comment' => 'Polymorphic type: StaffUser, Customer, etc.',
        ])->addColumn('actor_id', 'integer', [
            'signed' => false,
            'comment' => 'Polymorphic ID of the actor',
        ])->addColumn('action', 'string', [
            'limit' => 255,
            'null' => false,
            'comment' => 'Action performed',
        ])->addColumn('metadata', 'text', [
            'null' => false,
            'comment' => 'JSON metadata (optional)',
        ])->addColumn('ip_address', 'string', [
            'limit' => 45,
            'comment' => 'IP address of the request',
        ])->addColumn('user_agent', 'text', [
            'comment' => 'User agent string',
        ])->addColumn('created_at', 'datetime', [
            'null' => false,
            'comment' => 'Time the log was recorded',
        ])->create();
    }
}
