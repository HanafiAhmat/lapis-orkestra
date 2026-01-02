<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRevokedTokensTable extends AbstractMigration
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
        $table = $this->table('revoked_tokens', [
            'id' => 'revoked_token_id',
            'comment' => 'For JWT token invalidation tracking',
        ]);

        $table->addColumn('jti', 'string', [
            'limit' => 64,
            'comment' => 'JWT Token ID',
        ])->addColumn('user_type', 'string', [
            'limit' => 32,
            'comment' => 'staff_user, customer, etc.',
        ])->addColumn('user_id', 'integer', [
            'signed' => false,
            'comment' => 'User ID that issued the token',
        ])->addColumn('revoked_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'When was this token revoked',
        ])->addIndex(['jti'], [
            'unique' => true,
            'name' => 'revoked_tokens_jti_unique_index',
        ])->create();
    }
}
