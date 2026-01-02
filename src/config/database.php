<?php declare(strict_types=1);

use BitSynama\Lapis\Framework\Foundation\Env;

return [
    // Default connection name
    'default' => Env::string('DB_DRIVER', 'sqlite'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => Env::string('DB_HOST', '127.0.0.1'),
            'port' => Env::int('DB_PORT', 3306),
            'database' => Env::string('DB_DATABASE', 'lapis_orkestra'),
            'username' => Env::string('DB_USERNAME', 'admin'),
            'password' => Env::string('DB_PASSWORD', 'admin'),
            'charset' => Env::string('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::string('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => Env::string('DB_PREFIX'),
            'strict' => Env::bool('DB_STRICT', true),
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'sqlite_dir' => Env::string('DB_SQLITE_DIR', ''),
            'sqlite_name' => Env::string('DB_SQLITE_NAME', 'lapis_orkestra'),
            'sqlite_ext' => Env::string('DB_SQLITE_EXT', '.sqlite'),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => Env::string('DB_HOST', '127.0.0.1'),
            'port' => Env::int('DB_PORT', 5432),
            'database' => Env::string('DB_DATABASE', 'lapis_orkestra'),
            'username' => Env::string('DB_USERNAME', 'admin'),
            'password' => Env::string('DB_PASSWORD', 'admin'),
            'charset' => Env::string('DB_CHARSET', 'utf8mb4'),
            'collation' => Env::string('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => Env::string('DB_PREFIX'),
            'schema' => Env::string('DB_SCHEMA', 'public'),
        ],
    ],

    // If you want Eloquent model events (created/updated), set true:
    // 'events' => true,
];
