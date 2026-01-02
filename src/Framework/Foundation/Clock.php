<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use function is_numeric;

class Clock
{
    /**
     * Get current timestamp (mutable Carbon).
     */
    public static function now(): Carbon
    {
        return Carbon::now();
    }

    /**
     * Get current timestamp (immutable).
     */
    public static function nowImmutable(): CarbonImmutable
    {
        return Carbon::now()->toImmutable();
    }

    /**
     * Parse datetime string to Carbon instance.
     */
    public static function parse(string|int $datetime): Carbon
    {
        if (is_numeric($datetime)) {
            return Carbon::createFromTimestampUTC($datetime);
        }

        return Carbon::parse($datetime);
    }

    /**
     * Format datetime string.
     */
    public static function format(string|int $datetime, string $format = 'Y-m-d H:i:s'): string
    {
        return self::parse($datetime)->format($format);
    }

    /**
     * Get future timestamp after X seconds.
     */
    public static function addSeconds(int $seconds): Carbon
    {
        return self::now()->addSeconds($seconds);
    }

    /**
     * Get future timestamp after X minutes.
     */
    public static function addMinutes(int $minutes): Carbon
    {
        return self::now()->addMinutes($minutes);
    }

    /**
     * Get future timestamp after X hours.
     */
    public static function addHours(int $hours): Carbon
    {
        return self::now()->addHours($hours);
    }

    /**
     * Get future timestamp after X days.
     */
    public static function addDays(int $days): Carbon
    {
        return self::now()->addDays($days);
    }

    /**
     * Get future timestamp after X weeks.
     */
    public static function addWeeks(int $weeks): Carbon
    {
        return self::now()->addWeeks($weeks);
    }

    /**
     * Get future timestamp after X months.
     */
    public static function addMonths(int $months): Carbon
    {
        return self::now()->addMonths($months);
    }

    /**
     * Get future timestamp after X years.
     */
    public static function addYears(int $years): Carbon
    {
        return self::now()->addYears($years);
    }
}
