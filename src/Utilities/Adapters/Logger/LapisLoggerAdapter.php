<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Adapters\Logger;

use BitSynama\Lapis\Framework\Attributes\ImplementsPSR;
use BitSynama\Lapis\Framework\Foundation\Clock;
use BitSynama\Lapis\Utilities\AdapterInfo;
use BitSynama\Lapis\Utilities\Contracts\LoggerAdapterInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;
use Throwable;
use function array_key_exists;
use function array_map;
use function explode;
use function file_put_contents;
use function is_string;
use function sprintf;
use function strtolower;
use function strtoupper;
use const DIRECTORY_SEPARATOR;
use const FILE_APPEND;
use const LOCK_EX;

#[ImplementsPSR(
    LoggerInterface::class,
    psr: 'PSR-3',
    usage: 'Implements LoggerInterface through Psr\Log\AbstractLogger',
    link: 'https://www.php-fig.org/psr/psr-3/#3-psrlogloggerinterface'
)]
#[ImplementsPSR(
    LogLevel::class,
    psr: 'PSR-3',
    usage: 'Implements LogLevel',
    link: 'https://www.php-fig.org/psr/psr-3/#5-psrlogloglevel'
)]
#[AdapterInfo(type: 'logger', key: 'lapis', description: 'Custom minimal PSR-3 Logger')]
class LapisLoggerAdapter extends AbstractLogger implements LoggerAdapterInterface
{
    /**
     * @param string $logFilesDir  Directory where log files will be written.
     * @param string $channel  PSR-3 “channel” name, e.g. 'app'.
     */
    public function __construct(
        private readonly string $logFilesDir,
        private readonly string $channel = 'app',
        private readonly string $minLevel = 'debug'
    ) {
    }

    public function getLogger(): LoggerInterface
    {
        return $this;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        if (! $this->shouldLog($level)) {
            return;
        }

        $levelText = is_string($level) ? strtoupper($level) : 'unknown';
        $context = array_map(fn ($value) => $value instanceof Throwable ? $value->__toString() : $value, $context);
        if (! empty($context)) {
            if (array_key_exists('exception', $context)) {
                /** @var string $contexts */
                $contexts = $context['exception'];

                /** @var array<int, string> $contexts */
                $contexts = explode("\n", $contexts);
                $context = $contexts[0];
            } else {
                // $context = '>>> exception information not available <<<';
                $context = '';
            }
        } else {
            $context = '';
        }

        $entry = sprintf(
            "[%s] %s: %s %s\n",
            Clock::now()->toISOString(true),
            $this->channel . '.' . $levelText,
            (string) $message,
            (string) $context
        );

        file_put_contents(
            $this->logFilesDir . DIRECTORY_SEPARATOR . $levelText . '.log',
            $entry,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param mixed $level
     */
    protected function shouldLog($level): bool
    {
        $levels = [
            LogLevel::DEBUG => 0,
            LogLevel::INFO => 1,
            LogLevel::NOTICE => 2,
            LogLevel::WARNING => 3,
            LogLevel::ERROR => 4,
            LogLevel::CRITICAL => 5,
            LogLevel::ALERT => 6,
            LogLevel::EMERGENCY => 7,
        ];

        // normalize + validate level
        if (! is_string($level)) {
            return false;
        }
        $level = strtolower($level);

        if (! array_key_exists($level, $levels)) {
            return false;
        }

        $min = strtolower($this->minLevel);
        $minRank = $levels[$min] ?? $levels[LogLevel::DEBUG];

        return $levels[$level] >= $minRank;
    }
}
