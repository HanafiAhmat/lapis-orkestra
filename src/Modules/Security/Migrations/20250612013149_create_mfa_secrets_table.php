<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMfaSecretsTable extends AbstractMigration
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
        $table = $this->table('mfa_secrets', [
            'id' => 'mfa_secret_id',
            'comment' => 'For Multi Factor Authentication keys',
        ]);

        $table->addColumn('user_id', 'integer', [
            'signed' => false,
            'null' => false,
            'comment' => 'ID of the user (Customer or StaffUser)',
        ])->addColumn('user_type', 'string', [
            'limit' => 32,
            'null' => false,
            'comment' => 'User type: StaffUser, Customer, etc.',
        ])->addColumn('type', 'string', [
            'limit' => 12,
            'null' => false,
            'comment' => 'otp type: totp, email, sms',
        ])->addColumn('secret', 'string', [
            'limit' => 128,
            'null' => false,
            'comment' => 'Secret used to generate/verify OTPs (base32 or code)',
        ])->addColumn('device_fingerprint', 'string', [
            'comment' => 'Hash of device fingerprint if OTP was remembered',
        ])->addColumn('trusted_until', 'datetime', [
            'comment' => 'Datetime until this device is trusted (skips OTP)',
        ])->addColumn('last_sent_at', 'datetime', [
            'null' => false,
            'comment' => 'Datetime of last OTP sent (for email/sms)',
        ])->addColumn('verified_at', 'datetime', [
            'comment' => 'Datetime when MFA was verified successfully',
        ])->addColumn('enabled', 'boolean', [
            'default' => false,
            'comment' => 'Whether this MFA method is currently active',
        ])->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'Time record was created',
        ])->addColumn('updated_at', 'datetime', [
            'comment' => 'Time record was last updated',
        ])->addIndex(
            ['user_type', 'user_id', 'type'],
            [
                'unique' => true,
                'name' => 'mfa_secrets_user_type_unique',
            ]
        )->create();
    }
}
