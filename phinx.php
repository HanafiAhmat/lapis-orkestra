<?php declare(strict_types=1);

use BitSynama\Lapis\Lapis;

if (! isset($GLOBALS['__composer_autoload_files'])) {
    $vendorAutoload = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'autoload.php';
    if (! is_dir($vendorAutoload)) {
        $vendorAutoload = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }

    if (! is_dir($vendorAutoload)) {
        die("Vendor packages have not been installed.");
    }

    require_once $vendorAutoload;
}

Lapis::boot(); // so config/database.php is available

$dbConfig   = Lapis::configRegistry()->get('database');
$defaultKey = $dbConfig['default'];           // e.g. "mysql" or "sqlite"
$connInfo   = $dbConfig['connections'][$defaultKey];

// Build an array of migration paths: core first, modules second, app last
$repoDir = Lapis::varRegistry()->get('repo_dir');
$ds = DIRECTORY_SEPARATOR;
$migrationDirs = [];
$seedDirs      = [];

// core migrations/seeds
$migrationDirs[] = "{$repoDir}{$ds}src{$ds}Core{$ds}Migrations";
$seedDirs[]      = "{$repoDir}{$ds}src{$ds}Core{$ds}Seeds";

// child-app migrations/seeds if you allow app-specific ones
$projectDir = Lapis::varRegistry()->get('project_dir');
if (is_dir("{$projectDir}{$ds}app{$ds}Core{$ds}Migrations")) {
    $migrationDirs[] = "{$projectDir}{$ds}app{$ds}Core{$ds}Migrations";
}
if (is_dir("{$projectDir}{$ds}app{$ds}Core{$ds}Seeds")) {
    $seedDirs[] = "{$projectDir}{$ds}app{$ds}Core{$ds}Seeds";
}

$modules = Lapis::configRegistry()->get('modules') ?? [];
foreach ($modules as $compositeKey => $moduleDefinition) {
    if (! $moduleDefinition->enabled) {
        continue;
    }

    $modPath = $moduleDefinition->path;
    if (is_dir("{$modPath}{$ds}Migrations")) {
        $migrationDirs[] = "{$modPath}{$ds}Migrations";
    }
    if (is_dir("{$modPath}{$ds}Seeds")) {
        $seedDirs[] = "{$modPath}{$ds}Seeds";
    }
}

if ($connInfo->driver === 'sqlite') {
    $defaultSqliteDir = $projectDir . $ds . 'tmp' . $ds . 'database';
    $sqliteDir = $connInfo->sqlite_dir ?: $defaultSqliteDir;
    if (! is_dir($sqliteDir)) {
        if (! mkdir($sqliteDir, 0744, true)) {
            throw new RuntimeException('Failed to create database directory...');
        }
    }
    $connection = [
        'adapter' => 'sqlite',
        'name'    => $sqliteDir . $ds . $connInfo->sqlite_name,
        'suffix'  => $connInfo->sqlite_ext
    ];
} else {
    $connection = [
        'adapter'      => $connInfo->driver,      // "mysql", "pgsql", etc.
        'host'         => $connInfo->host,
        'name'         => $connInfo->database,
        'user'         => $connInfo->username,
        'pass'         => $connInfo->password,
        'port'         => $connInfo->port,
        'charset'      => $connInfo->charset,
        'collation'    => $connInfo->collation,
        'table_prefix' => $connInfo->prefix,
    ];

    if ($connInfo->driver == 'pgsql' && !empty($connInfo->schema)) {
        $connection['schema'] = $connInfo->schema;
    }
}

return [
    'paths' => [
        'migrations' => $migrationDirs,
        'seeds'      => $seedDirs,
    ],
    'environments' => [
        // “default_migration_table” can stay fixed; we only have one environment
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'default',

        // The single environment named “default” (no production vs. development distinction)
        'default' => $connection,
    ],
];
