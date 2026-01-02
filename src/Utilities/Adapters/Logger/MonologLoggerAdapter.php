<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Logger;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\LoggerAdapterInterface;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonoLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Stringable;
use function is_dir;
use function mkdir;
use function rtrim;
use const DIRECTORY_SEPARATOR;

#[ImplementsPSR(
    LoggerInterface::class,
    psr: 'PSR-3',
    usage: 'Implements LoggerInterface through Monolog\Logger',
    link: 'https://www.php-fig.org/psr/psr-3/#3-psrlogloggerinterface'
)]
#[ImplementsPSR(
    LoggerAwareInterface::class,
    psr: 'PSR-3',
    usage: 'Implements LogAwareInterface through Monolog\Logger',
    link: 'https://www.php-fig.org/psr/psr-3/#5-psrlogloglevel'
)]
#[ImplementsPSR(
    LogLevel::class,
    psr: 'PSR-3',
    usage: 'Implements LogLevel through Monolog\Logger',
    link: 'https://www.php-fig.org/psr/psr-3/#4-psrlogloggerawareinterface'
)]
#[AdapterInfo(type: 'logger', key: 'monolog', description: 'Monolog PSR-3 Logger')]
final class MonologLoggerAdapter implements LoggerAdapterInterface
{
    private readonly MonoLogger $logger;

    /**
     * @param string $logFilesDir  Directory where log files will be written.
     * @param string $channel  PSR-3 “channel” name, e.g. 'app'.
     */
    public function __construct(
        private readonly string $logFilesDir,
        private readonly string $channel = 'app',
        private readonly string $minLevel = 'debug'
    ) {
        $this->logger = new MonoLogger($this->channel);
        $this->configureHandlers();
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::DEBUG)) {
            return;
        }

        $this->logger->debug($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::INFO)) {
            return;
        }

        $this->logger->info($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::NOTICE)) {
            return;
        }

        $this->logger->notice($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::WARNING)) {
            return;
        }

        $this->logger->warning($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::ERROR)) {
            return;
        }

        $this->logger->error($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::CRITICAL)) {
            return;
        }

        $this->logger->critical($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::ALERT)) {
            return;
        }

        $this->logger->alert($message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog(LogLevel::EMERGENCY)) {
            return;
        }

        $this->logger->emergency($message, $context);
    }

    private function configureHandlers(): void
    {
        // Ensure base directory exists
        if (! is_dir($this->logFilesDir) && ! mkdir($this->logFilesDir, 0755, true)) {
            throw new RuntimeException("Could not create log directory: {$this->logFilesDir}");
        }

        // One file per log level
        $fileList = [
            Level::fromName(LogLevel::DEBUG)->value => 'debug.log',
            Level::fromName(LogLevel::INFO)->value => 'info.log',
            Level::fromName(LogLevel::NOTICE)->value => 'notice.log',
            Level::fromName(LogLevel::WARNING)->value => 'warning.log',
            Level::fromName(LogLevel::ERROR)->value => 'error.log',
            Level::fromName(LogLevel::CRITICAL)->value => 'critical.log',
            Level::fromName(LogLevel::ALERT)->value => 'alert.log',
            Level::fromName(LogLevel::EMERGENCY)->value => 'emergency.log',
        ];

        foreach ($fileList as $levelConst => $filename) {
            $path = rtrim($this->logFilesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            $stream = new StreamHandler($path, $levelConst);
            // FilterHandler ensures only this exact level is logged to this file
            $filter = new FilterHandler($stream, $levelConst, $levelConst);
            $this->logger->pushHandler($filter);
        }
    }

    private function shouldLog(string $level): bool
    {
        $levels = [
            LogLevel::DEBUG => Level::fromName(LogLevel::DEBUG)->value,
            LogLevel::INFO => Level::fromName(LogLevel::INFO)->value,
            LogLevel::NOTICE => Level::fromName(LogLevel::NOTICE)->value,
            LogLevel::WARNING => Level::fromName(LogLevel::WARNING)->value,
            LogLevel::ERROR => Level::fromName(LogLevel::ERROR)->value,
            LogLevel::CRITICAL => Level::fromName(LogLevel::CRITICAL)->value,
            LogLevel::ALERT => Level::fromName(LogLevel::ALERT)->value,
            LogLevel::EMERGENCY => Level::fromName(LogLevel::EMERGENCY)->value,
        ];

        return $levels[$level] >= $levels[$this->minLevel];
    }
}
