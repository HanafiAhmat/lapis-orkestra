<?php declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateDeviceFingerprintsTable extends AbstractMigration
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
        $table = $this->table('device_fingerprints', [
            'id' => 'device_fingerprint_id',
            'comment' => 'For Device Fingerprints tracking',
        ]);

        $table->addColumn('user_type', 'string', [
            'limit' => 32,
            'comment' => 'staff_user, customer, etc.',
        ])->addColumn('user_id', 'integer', [
            'signed' => false,
            'comment' => 'User ID that used the device',
        ])->addColumn('fingerprint', 'string', [
            'comment' => 'Device information',
        ])->addColumn('user_agent', 'string', [
            'null' => true,
            'comment' => 'User agent used by the device',
        ])->addColumn('ip_address', 'string', [
            'limit' => 64,
            'null' => true,
            'comment' => 'Captured user IP address',
        ])->addColumn('last_seen_at', 'datetime', [
            'comment' => 'Last seen using this device',
        ])->addColumn('created_at', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'comment' => 'First seen using this device',
        ])->addIndex(
            ['user_type', 'user_id', 'fingerprint'],
            [
                'unique' => true,
                'name' => 'device_fingerprints_unique_index',
            ]
        )->create();
    }
}
