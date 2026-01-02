<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\DTO\Configs\Utilities;

class DatabaseConnectionConfig
{
    public function __construct(
        public string $name = 'default',
        public string $driver = 'mysql',
        public string $host = '127.0.0.1',
        public int $port = 3306,
        public string $database = 'lapis_orkestra',
        public string $username = 'admin',
        public string $password = 'admin',
        public string $charset = 'utf8mb4',
        public string $collation = 'utf8mb4_unicode_ci',
        public string $prefix = '',
        public bool $strict = true,
        public string $sqlite_dir = '',
        public string $sqlite_name = 'lapis_orkestra',
        public string $sqlite_ext = '.sqlite',
        public string $schema = 'public'
    ) {
    }

    /**
     * @param array<string, scalar> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: self::scalarToString($data['name'] ?? 'default'),
            driver: self::scalarToString($data['driver'] ?? 'mysql'),
            host: self::scalarToString($data['host'] ?? '127.0.0.1'),

            // port must be int, allow numeric strings too
            port: (int) ($data['port'] ?? 3306),
            database: self::scalarToString($data['database'] ?? 'lapis_orkestra'),
            username: self::scalarToString($data['username'] ?? 'admin'),
            password: self::scalarToString($data['password'] ?? 'admin'),
            charset: self::scalarToString($data['charset'] ?? 'utf8mb4'),
            collation: self::scalarToString($data['collation'] ?? 'utf8mb4_unicode_ci'),
            prefix: self::scalarToString($data['prefix'] ?? ''),

            // strict must be bool; accept "0/1", "true/false" loosely via (bool)
            strict: (bool) ($data['strict'] ?? true),
            sqlite_dir: self::scalarToString($data['sqlite_dir'] ?? ''),
            sqlite_name: self::scalarToString($data['sqlite_name'] ?? 'lapis_orkestra'),
            sqlite_ext: self::scalarToString($data['sqlite_ext'] ?? '.sqlite'),
            schema: self::scalarToString($data['schema'] ?? 'public'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'prefix' => $this->prefix,
            'strict' => $this->strict,
            'sqlite_dir' => $this->sqlite_dir,
            'sqlite_name' => $this->sqlite_name,
            'sqlite_ext' => $this->sqlite_ext,
            'schema' => $this->schema,
        ];
    }

    /**
     * @param scalar $value
     */
    private static function scalarToString($value): string
    {
        return (string) $value;
    }
}
