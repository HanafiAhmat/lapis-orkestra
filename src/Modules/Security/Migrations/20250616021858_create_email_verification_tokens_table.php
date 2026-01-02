<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateEmailVerificationTokensTable extends AbstractMigration
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
        $table = $this->table('email_verification_tokens', [
            'id' => 'email_verification_token_id',
            'comment' => 'Email verification tokens table',
        ]);

        $table->addColumn('user_type', 'string', [
            'limit' => 32,
            'comment' => 'staff, customer, etc.',
        ])->addColumn('user_id', 'integer', [
            'signed' => false,
            'comment' => 'Related User ID',
        ])->addColumn('token_hash', 'string', [
            'length' => 255,
            'comment' => 'Email verification hashed token.',
        ])->addColumn('expires_at', 'datetime', [
            'comment' => 'Date and time token will expire.',
        ])->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Date and time token record was created.',
        ])->addIndex(
            ['token_hash'],
            [
                'unique' => true,
                'name' => 'email_verification_tokens_token_hash_unique_index',
            ]
        )->create();
    }
}
