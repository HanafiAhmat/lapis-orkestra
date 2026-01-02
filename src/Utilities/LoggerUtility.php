<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities;

use BitSynama\Lapis\Framework\DTO\Configs\Utilities\LoggerConfig;
use BitSynama\Lapis\Framework\Foundation\Atlas;
use BitSynama\Lapis\Lapis;
use BitSynama\Lapis\Utilities\Contracts\LoggerAdapterInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Stringable;
use function is_dir;
use function mkdir;
use const DIRECTORY_SEPARATOR;

/**
 * LoggerUtility now discovers all PSR-3 adapters (by AdapterInfo attribute)
 * in both the framework and child-app, then instantiates the chosen adapter based on config.
 */
class LoggerUtility
{
    private readonly LoggerAdapterInterface $adapter;

    public function __construct()
    {
        $this->adapter = $this->discoverAndInstantiate();
    }

    /**
     * Returns the PSR-3 logger from the chosen adapter.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->adapter->getLogger();
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->adapter->debug($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->adapter->info($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->adapter->notice($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->adapter->warning($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->adapter->error($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->adapter->critical($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->adapter->alert($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->adapter->emergency($message, $context);
    }

    /**
     * Scans framework and child-app adapter directories for classes annotated with AdapterInfo,
     * and instantiates the one matching $adapterKey.
     */
    private function discoverAndInstantiate(): LoggerAdapterInterface
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');

        /** @var string $projectDir */
        $projectDir = Lapis::varRegistry()->get('project_dir');

        /** @var string $tmpDir */
        $tmpDir = Lapis::varRegistry()->get('tmp_dir');

        /** @var LoggerConfig $loggerConfig */
        $loggerConfig = Lapis::configRegistry()->get('utility.logger');

        /** @var string $adapterKey */
        $adapterKey = $loggerConfig->adapter ?? 'lapis';

        /** @var string $channel */
        $channel = $loggerConfig->channel ?? 'app';

        /** @var string $logMinLevel */
        $logMinLevel = $loggerConfig->level ?? 'debug';

        $defaultLogsDir = $tmpDir . DIRECTORY_SEPARATOR . 'logs';
        /** @var string $logsDir */
        $logsDir = $loggerConfig->logs_dir ?: $defaultLogsDir;
        if (! is_dir($logsDir)) {
            if (! mkdir($logsDir, 0755, true)) {
                throw new RuntimeException('Failed to create log directories...');
            }
        }

        $className = Atlas::discover(
            dirPath: 'Utilities.Adapters.Logger',
            interface: LoggerAdapterInterface::class,
            attribute: AdapterInfo::class,
            classSuffix: 'LoggerAdapter',
            type: 'logger',
            key: $adapterKey,
            repoDir: $repoDir,
            projectDir: $projectDir
        );

        if (empty($className)) {
            throw new RuntimeException("Logger adapter '{$adapterKey}' not found.");
        }

        /** @var LoggerAdapterInterface $instance */
        $instance = new $className($logsDir, $channel, $logMinLevel);

        return $instance;
    }
}
