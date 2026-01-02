<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetTokensTable extends AbstractMigration
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
        $table = $this->table('password_reset_tokens', [
            'id' => 'password_reset_token_id',
            'comment' => 'Password reset tokens table',
        ]);

        $table->addColumn('user_type', 'string', [
            'limit' => 32,
            'comment' => 'staff, customer, etc.',
        ])->addColumn('user_id', 'integer', [
            'signed' => false,
            'comment' => 'Related User ID',
        ])->addColumn('token_hash', 'string', [
            'comment' => 'Password reset request token.',
        ])->addColumn('client_type', 'string', [
            'limit' => 50,
            'comment' => 'Either web, mobile or postman.',
        ])->addColumn('user_agent', 'text', [
            'comment' => 'User agent used to log in.',
        ])->addColumn('ip_address', 'string', [
            'limit' => 100,
            'comment' => 'IP address user logged in at.',
        ])->addColumn('expires_at', 'datetime', [
            'comment' => 'Date and time token will expire.',
        ])->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Date and time token record was created.',
        ])->addIndex(
            ['token_hash'],
            [
                'unique' => true,
                'name' => 'password_reset_tokens_token_hash_unique_index',
            ]
        )->create();
    }
}
