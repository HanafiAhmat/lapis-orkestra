<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

final class DbReadiness
{
    // MySQL: 'phinxlog', SQLite: same unless you renamed it
    private const PHINX_LOG_TABLE = 'phinxlog';

    /**
     * Returns true if DB is reachable and migrations have run at least once.
     */
    public static function isReady(): bool
    {
        try {
            $schema = DB::schema();
            return $schema->hasTable(self::PHINX_LOG_TABLE);
        } catch (Throwable) {
            // Connection not even available yet
            return false;
        }
    }
}
