<?php

namespace App\Support;

class Format
{
    /** 1234 → "1.2K", 2_400_000 → "2.4M". */
    public static function compact(int|float|null $value): string
    {
        if ($value === null) {
            return '—';
        }

        $abs = abs($value);

        return match (true) {
            $abs >= 1_000_000_000 => self::trim($value / 1_000_000_000).'B',
            $abs >= 1_000_000 => self::trim($value / 1_000_000).'M',
            $abs >= 10_000 => self::trim($value / 1_000, 0).'K',
            $abs >= 1_000 => self::trim($value / 1_000).'K',
            default => number_format($value),
        };
    }

    /** 3.8 → "3.8%". */
    public static function percent(?float $value, int $decimals = 1): string
    {
        return $value === null ? '—' : number_format($value, $decimals).'%';
    }

    /** Seconds → "6:14" or "8.4 sec" for short clips. */
    public static function duration(?float $seconds): string
    {
        if ($seconds === null) {
            return '—';
        }

        if ($seconds < 60) {
            return number_format($seconds, 1).' sec';
        }

        $seconds = (int) round($seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }

    private static function trim(float $value, int $decimals = 1): string
    {
        $formatted = number_format($value, $decimals);

        return $decimals > 0 ? rtrim(rtrim($formatted, '0'), '.') : $formatted;
    }
}
