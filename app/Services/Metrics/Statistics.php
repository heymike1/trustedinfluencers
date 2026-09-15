<?php

namespace App\Services\Metrics;

class Statistics
{
    /**
     * @param  array<int|float>  $values
     */
    public static function median(array $values): ?float
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== null));

        if ($values === []) {
            return null;
        }

        sort($values);
        $count = count($values);
        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float) $values[$middle];
        }

        return ($values[$middle - 1] + $values[$middle]) / 2;
    }

    /**
     * @param  array<int|float>  $values
     */
    public static function mean(array $values): ?float
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== null));

        if ($values === []) {
            return null;
        }

        return array_sum($values) / count($values);
    }
}
