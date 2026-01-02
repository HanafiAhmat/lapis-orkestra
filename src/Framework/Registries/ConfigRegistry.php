<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Registries;

use App\Framework\DTO\DatabaseConnectionConfig as AppDatabaseConnectionConfig;
use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CacheConfig;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\CookieConfig;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\DatabaseConnectionConfig;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\LoggerConfig;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\SessionConfig;
use BitSynama\Lapis\Framework\DTO\Configs\Utilities\ViewConfig;
use BitSynama\Lapis\Lapis;
use DirectoryIterator;
use Psr\Container\ContainerInterface;
use function array_key_exists;
use function array_replace_recursive;
use function array_splice;
use function class_exists;
use function explode;
use function implode;
use function is_array;
use function is_bool;
use function is_dir;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function property_exists;
use function str_replace;
use function strtolower;
use const DIRECTORY_SEPARATOR;

#[ImplementsPSR(
    ContainerInterface::class,
    psr: 'PSR-11',
    usage: 'Implements from Container Interface',
    link: 'https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface'
)]
#[ImplementsPSR(
    ContainerInterface::class,
    psr: 'PSR-11',
    usage: 'Implemented get() method',
    link: 'https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface'
)]
#[ImplementsPSR(
    ContainerInterface::class,
    psr: 'PSR-11',
    usage: 'Implemented has() method',
    link: 'https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface'
)]
final class ConfigRegistry implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [];

    public function load(): void
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $repoConfigDir */
        $repoConfigDir = implode(DIRECTORY_SEPARATOR, [$repoDir, 'src', 'config']);

        /** @var string $repoDtoDir */
        $repoDtoDir = implode(DIRECTORY_SEPARATOR, [$repoDir, 'src', 'Framework', 'DTO']);

        // Load core config
        foreach (new DirectoryIterator($repoConfigDir) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir() || $fileInfo->getExtension() !== 'php') {
                continue;
            }

            $filename = strtolower(str_replace('.php', '', $fileInfo->getFilename()));
            if ($filename === 'modules') {
                continue;
            }

            $tmpConfig = require $fileInfo->getPathname();
            if (! is_array($tmpConfig)) {
                // ignore invalid config file output
                continue;
            }

            switch ($filename) {
                case 'database':
                    $connections = $tmpConfig['connections'] ?? null;
                    if (! is_array($connections)) {
                        $connections = [];
                    }

                    /** @var array<string, scalar> $driverConfig */
                    foreach ($connections as $driverKey => $driverConfig) {
                        if (! is_string($driverKey) || ! is_array($driverConfig)) {
                            continue;
                        }

                        $connections[$driverKey] = DatabaseConnectionConfig::fromArray($driverConfig);
                    }

                    $tmpConfig['connections'] = $connections;
                    break;

                case 'utility':
                    foreach (['cache', 'cookie', 'logger', 'session', 'view'] as $utility) {
                        $uRaw = $tmpConfig[$utility] ?? null;
                        if (! is_array($uRaw)) {
                            continue;
                        }

                        // normalize keys
                        $u = $this->toStringKeyedArray($uRaw);

                        switch ($utility) {
                            case 'cache':
                                $tmpConfig['cache'] = CacheConfig::fromArray($u);
                                break;

                            case 'cookie':
                                $tmpConfig['cookie'] = CookieConfig::fromArray($u);
                                break;

                            case 'logger':
                                // LoggerConfig wants array<string,string>
                                $tmpConfig['logger'] = LoggerConfig::fromArray($this->toStringMap($uRaw));
                                break;

                            case 'session':
                                $tmpConfig['session'] = SessionConfig::fromArray($u);
                                break;

                            case 'view':
                                $tmpConfig['view'] = ViewConfig::fromArray($u);
                                break;
                        }
                    }
                    break;
            }
            $this->config[$filename] = $tmpConfig;
        }

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $repoConfigDir */
        $projectConfigDir = implode(DIRECTORY_SEPARATOR, [$projectDir, 'app', 'config']);

        /** @var string $repoDtoDir */
        $projectDtoDir = implode(DIRECTORY_SEPARATOR, [$projectDir, 'app', 'Framework', 'DTO']);

        // Load project config
        if (! is_dir($projectConfigDir)) {
            return;
        }
        foreach (new DirectoryIterator($projectConfigDir) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir() || $fileInfo->getExtension() !== 'php') {
                continue;
            }

            $filename = strtolower(str_replace('.php', '', $fileInfo->getFilename()));
            if ($filename === 'modules') {
                continue;
            }

            $tmpConfig = require $fileInfo->getPathname();
            if (! is_array($tmpConfig)) {
                continue;
            }

            $availableConfig = $this->config[$filename] ?? [];
            if (! is_array($availableConfig)) {
                $availableConfig = [];
            }

            switch ($filename) {
                case 'database':
                    $connectionsRaw = $tmpConfig['connections'] ?? null;
                    if (! is_array($connectionsRaw)) {
                        break;
                    }

                    $bucketKey = 'database';

                    /** @var array<string, array<mixed,mixed>> $connections */
                    $connections = [];
                    foreach ($connectionsRaw as $driverKey => $driverConfig) {
                        if (is_string($driverKey) && is_array($driverConfig)) {
                            $connections[$driverKey] = $driverConfig;
                        }
                    }

                    // Narrow config bucket before offsets
                    if (! isset($this->config[$bucketKey]) || ! is_array($this->config[$bucketKey])) {
                        $this->config[$bucketKey] = [];
                    }

                    /** @var array<string, mixed> $dbConfig */
                    $dbConfig = $this->config[$bucketKey];

                    if (! isset($dbConfig['connections']) || ! is_array($dbConfig['connections'])) {
                        $dbConfig['connections'] = [];
                    }

                    /** @var array<string, object> $existingConnections */
                    $existingConnections = $dbConfig['connections'];

                    /** @var array<string, scalar> $driverConfig */
                    foreach ($connections as $driverKey => $driverConfig) {
                        $existing = $existingConnections[$driverKey] ?? null;

                        if (is_object($existing)) {
                            foreach ($driverConfig as $key => $value) {
                                if (is_string($key)) {
                                    $existing->{$key} = $value;
                                }
                            }
                            $existingConnections[$driverKey] = $existing;
                            continue;
                        }

                        $existingConnections[$driverKey] = class_exists(AppDatabaseConnectionConfig::class)
                            ? AppDatabaseConnectionConfig::fromArray($driverConfig)
                            : DatabaseConnectionConfig::fromArray($driverConfig);
                    }

                    // write back
                    $dbConfig['connections'] = $existingConnections;
                    $this->config[$bucketKey] = $dbConfig;
                    break;

                case 'utility':
                    // intentionally not allowing override here (per your note)
                    break;

                default:
                    $this->config[$filename] = array_replace_recursive($availableConfig, $tmpConfig);
                    break;
            }
        }
    }

    /**
     * Set a configuration value using dot notation.
     *
     * @param string $dotNotationKey Dot notation key (e.g., 'app.env')
     * @param mixed $value The value to set
     */
    public function set(string $dotNotationKey, mixed $value): void
    {
        if (empty($dotNotationKey)) {
            return;
        }

        $identifiers = explode('.', $dotNotationKey);
        $isUtility = $identifiers[0] === 'utility';
        $config = &$this->config;

        if ($isUtility) {
            $bucketKey = $identifiers[1] ?? '';
            if ($bucketKey === '') {
                return;
            }

            if (! isset($this->config['utility']) || ! is_array($this->config['utility'])) {
                $this->config['utility'] = [];
            }

            if (! isset($this->config['utility'][$bucketKey])) {
                $this->config['utility'][$bucketKey] = [];
            }

            // now safe: utility bucket is guaranteed array|object
            $config = &$this->config['utility'][$bucketKey];

            $splicedIdentifiers = array_splice($identifiers, 2);

            foreach ($splicedIdentifiers as $identifier) {
                if ($identifier === '') {
                    return;
                }

                if (is_object($config)) {
                    if (! property_exists($config, $identifier)) {
                        return;
                    }
                    $config = &$config->{$identifier};
                    continue;
                }

                if (! is_array($config)) {
                    $config = [];
                }

                if (! isset($config[$identifier]) || (! is_array($config[$identifier]) && ! is_object(
                    $config[$identifier]
                ))) {
                    $config[$identifier] = [];
                }

                $config = &$config[$identifier];
            }
        } else {
            foreach ($identifiers as $identifier) {
                if (! isset($config[$identifier]) || ! is_array($config[$identifier])) {
                    $config[$identifier] = [];
                }
                $config = &$config[$identifier];
            }
        }

        $config = $value;
    }

    /**
     * Get config value.
     */
    public function get(string $dotNotationKey): mixed
    {
        if ($dotNotationKey === '') {
            return null;
        }

        $identifiers = explode('.', $dotNotationKey);
        $isUtility = ($identifiers[0] ?? '') === 'utility';

        $config = $this->config;

        if ($isUtility) {
            $bucketKey = $identifiers[1] ?? '';
            if ($bucketKey === '') {
                return null;
            }

            $bucket = $this->utilityBucket($bucketKey);
            if ($bucket === null) {
                return null;
            }

            $rest = array_splice($identifiers, 2);

            $config2 = $bucket;
            foreach ($rest as $identifier) {
                if ($identifier === '') {
                    return null;
                }

                if (is_object($config2)) {
                    if (! property_exists($config2, $identifier)) {
                        return null;
                    }
                    $config2 = $config2->{$identifier};
                    continue;
                }

                // array
                if (! is_array($config2) || ! array_key_exists($identifier, $config2)) {
                    return null;
                }
                $config2 = $config2[$identifier];
            }

            return $config2;
        }

        // non-utility
        foreach ($identifiers as $identifier) {
            if ($identifier === '' || ! is_array($config) || ! array_key_exists($identifier, $config)) {
                return null;
            }
            $config = $config[$identifier];
        }

        return $config;
    }

    /**
     * Check if a config exists.
     */
    public function has(string $dotNotationKey): bool
    {
        if ($dotNotationKey === '') {
            return false;
        }

        $identifiers = explode('.', $dotNotationKey);
        $isUtility = ($identifiers[0] ?? '') === 'utility';

        $config = $this->config;

        if ($isUtility) {
            $bucketKey = $identifiers[1] ?? '';
            if ($bucketKey === '') {
                return false;
            }

            $bucket = $this->utilityBucket($bucketKey);
            if ($bucket === null) {
                return false;
            }

            $rest = array_splice($identifiers, 2);

            $config2 = $bucket;
            foreach ($rest as $identifier) {
                if ($identifier === '') {
                    return false;
                }

                if (is_object($config2)) {
                    if (! property_exists($config2, $identifier)) {
                        return false;
                    }
                    $config2 = $config2->{$identifier};
                    continue;
                }

                if (! is_array($config2) || ! array_key_exists($identifier, $config2)) {
                    return false;
                }
                $config2 = $config2[$identifier];
            }

            return true;
        }

        foreach ($identifiers as $identifier) {
            if ($identifier === '' || ! is_array($config) || ! array_key_exists($identifier, $config)) {
                return false;
            }
            $config = $config[$identifier];
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function utilityRoot(): array|null
    {
        $root = $this->config['utility'] ?? null;
        if (! is_array($root)) {
            return null;
        }

        /** @var array<string, mixed> $typed */
        $typed = $this->toStringKeyedArray($root);
        return $typed;
    }

    /**
     * @return object|array<string, mixed>|null
     */
    private function utilityBucket(string $key): object|array|null
    {
        $root = $this->utilityRoot();
        if ($root === null) {
            return null;
        }

        $bucket = $root[$key] ?? null;
        if (is_object($bucket)) {
            return $bucket;
        }

        if (is_array($bucket)) {
            /** @var array<string, mixed> $typed */
            $typed = $this->toStringKeyedArray($bucket);
            return $typed;
        }

        return null;
    }

    /**
     * Keep only string keys (PHPStan: array<string, mixed>).
     *
     * @param array<mixed, mixed> $arr
     * @return array<string, mixed>
     */
    private function toStringKeyedArray(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (is_string($k)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * Normalize to array<string,string> for LoggerConfig::fromArray()
     *
     * @param array<mixed, mixed> $arr
     * @return array<string, string>
     */
    private function toStringMap(array $arr): array
    {
        $out = [];
        foreach ($arr as $k => $v) {
            if (! is_string($k)) {
                continue;
            }

            if (is_string($v)) {
                $out[$k] = $v;
                continue;
            }

            if (is_int($v) || is_float($v) || is_bool($v)) {
                $out[$k] = (string) $v;
                continue;
            }

            // ignore non-scalar
        }
        return $out;
    }
}
