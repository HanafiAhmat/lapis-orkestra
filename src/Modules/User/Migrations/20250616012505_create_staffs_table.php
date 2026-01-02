<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStaffsTable extends AbstractMigration
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
        $table = $this->table('staffs', [
            'id' => 'staff_id',
            'comment' => 'Admin Staff table',
        ]);

        $table->addColumn('name', 'string', [
            'limit' => 150,
            'null' => true,
            'comment' => 'Display name of user.',
        ])->addColumn('email', 'string', [
            'limit' => 150,
            'null' => false,
            'comment' => 'Staff User email address.',
        ])->addColumn('password', 'string', [
            'limit' => 255,
            'comment' => 'Staff User hashed password.',
        ])->addColumn('email_verified_at', 'datetime', [
            'null' => true,
            'default' => null,
            'comment' => 'Date and time user email was verified.',
        ])->addColumn('role', 'string', [
            'limit' => 50,
            'null' => false,
            'default' => 'member',
            'comment' => 'Role of the staff user: superuser, manager, member.',
        ])->addColumn('status', 'string', [
            'limit' => 50,
            'null' => false,
            'default' => 'active',
            'comment' => 'Status of the staff user: active, inactive, suspended, banned.',
        ])->addColumn('suspended_at', 'datetime', [
            'null' => true,
            'default' => null,
            'comment' => 'Date and time staff user was suspended.',
        ])->addColumn('invitation_token', 'string', [
            'limit' => 255,
            'null' => true,
            'default' => null,
            'comment' => 'Invitation token for new staff user.',
        ])->addColumn('invitation_expires_at', 'datetime', [
            'null' => true,
            'default' => null,
            'comment' => 'Expiration time of the invitation.',
        ])->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Date and time user was created.',
        ])->addColumn('updated_at', 'datetime', [
            'comment' => 'Date and time staff user record was updated.',
        ])->addIndex(['email'], [
            'unique' => true,
            'name' => 'staffs_email_unique_index',
        ])->create();
    }
}
