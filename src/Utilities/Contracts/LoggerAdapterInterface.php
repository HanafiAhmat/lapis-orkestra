<?php declare(strict_types=1);

namespace BitSynama\Lapis\Utilities\Contracts;

use Psr\Log\LoggerInterface;
use Stringable;

/**
 * All logger adapter implementations must return a PSR-3 Logger.
 */
interface LoggerAdapterInterface
{
    /**
     * Return a PSR-3 compliant logger instance.
     */
    public function getLogger(): LoggerInterface;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function debug(string|Stringable $message, array $context = []): void;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function info(string|Stringable $message, array $context = []): void;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function notice(string|Stringable $message, array $context = []): void;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function warning(string|Stringable $message, array $context = []): void;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function error(string|Stringable $message, array $context = []): void;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function critical(string|Stringable $message, array $context = []): void;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function alert(string|Stringable $message, array $context = []): void;

    /**
     * @param string|Stringable $message The log message
     * @param mixed[]           $context The log context
     */
    public function emergency(string|Stringable $message, array $context = []): void;
}
