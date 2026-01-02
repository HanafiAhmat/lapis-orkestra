<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCustomersTable extends AbstractMigration
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
        $table = $this->table('customers', [
            'id' => 'customer_id',
            'comment' => 'Customer table',
        ]);

        $table->addColumn('name', 'string', [
            'limit' => 150,
            'null' => true,
            'comment' => 'Display name of customer.',
        ])->addColumn('email', 'string', [
            'limit' => 150,
            'null' => false,
            'comment' => 'Customer email address.',
        ])->addColumn('password', 'string', [
            'limit' => 255,
            'comment' => 'Customer hashed password.',
        ])->addColumn('email_verified_at', 'datetime', [
            'null' => true,
            'default' => null,
            'comment' => 'Date and time customer email was verified.',
        ])->addColumn('status', 'string', [
            'limit' => 50,
            'null' => false,
            'default' => 'active',
            'comment' => 'Status of the staff customer: active, inactive, suspended, banned.',
        ])->addColumn('suspended_at', 'datetime', [
            'null' => true,
            'default' => null,
            'comment' => 'Date and time staff customer was suspended.',
        ])->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Date and time customer was created.',
        ])->addColumn('updated_at', 'datetime', [
            'comment' => 'Date and time staff customer record was updated.',
        ])->addIndex(['email'], [
            'unique' => true,
            'name' => 'customers_email_unique_index',
        ])->create();
    }
}
