<?php declare(strict_types=1);

namespace BitSynama\Lapis\Framework\Foundation;

use Carbon\Carbon;
use function count;
use function is_array;
use function preg_split;
use function strtolower;
use function trim;

class TokenLifetime
{
    public static function parse(string $input, int $defaultMinutes = 15): Carbon
    {
        $datetime = Carbon::now()->addMinutes($defaultMinutes);

        $lifetimes = preg_split('/\s+/', trim($input));
        if (! is_array($lifetimes) || count($lifetimes) !== 2) {
            return $datetime;
        }

        $num = (int) $lifetimes[0];
        $unit = strtolower($lifetimes[1]);

        if ($num <= 0) {
            return $datetime;
        }

        return match ($unit) {
            'second', 'seconds' => Carbon::now()->addSeconds($num),
            'minute', 'minutes' => Carbon::now()->addMinutes($num),
            'hour', 'hours' => Carbon::now()->addHours($num),
            'day', 'days' => Carbon::now()->addDays($num),
            'week', 'weeks' => Carbon::now()->addWeeks($num),
            'month', 'months' => Carbon::now()->addMonths($num),
            'year', 'years' => Carbon::now()->addYears($num),
            default => $datetime,
        };
    }
}
