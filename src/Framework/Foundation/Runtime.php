<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use function array_map;
use function explode;
use function file_exists;
use function file_get_contents;
use function floor;
use function function_exists;
use function implode;
use function in_array;
use function ini_get;
use function memory_get_usage;
use function number_format;
use function round;
use function sys_getloadavg;
use const PHP_SAPI;

final class Runtime
{
    public static function uptime(): string|null
    {
        if (file_exists('/proc/uptime')) {
            /** @var string $uptimeContents */
            $uptimeContents = file_get_contents('/proc/uptime');
            $uptime = (int) explode(' ', $uptimeContents)[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);

            return "{$days}d {$hours}h";
        }

        return null;
    }

    public static function loadAverage(): string|null
    {
        $load = sys_getloadavg();

        return $load ? implode(', ', array_map(number_format(...), $load, [2, 2, 2])) : null;
    }

    /**
     * @return array<string, scalar>|null
     */
    public static function memoryUsage(): array|null
    {
        if (function_exists('memory_get_usage')) {
            $used = round(memory_get_usage(true) / 1048576, 1);
            $total = ini_get('memory_limit');

            return [
                'used' => "{$used} MB",
                'total' => $total,
            ];
        }

        return null;
    }

    public static function isCli(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    public static function isDev(): bool
    {
        return Env::string('APP_ENV') === 'development';
    }

    public static function isSta(): bool
    {
        return Env::string('APP_ENV') === 'staging';
    }

    public static function isProd(): bool
    {
        return Env::string('APP_ENV') === 'production';
    }

    public static function isDebug(): bool
    {
        return Env::bool('APP_DEBUG');
    }

    public static function consoleWantsJson(): bool
    {
        if (! self::isCli()) {
            return false;
        }
        global $argv;
        return in_array('--json', $argv ?? [], true);
    }
}
