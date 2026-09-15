<?php

namespace App\Enums;

enum MetricWindow: string
{
    case Last10 = 'last_10';
    case Last20 = 'last_20';
    case Last30 = 'last_30';
    case Last30Days = 'last_30_days';
    case Last90Days = 'last_90_days';

    public function label(): string
    {
        return match ($this) {
            self::Last10 => 'Last 10',
            self::Last20 => 'Last 20',
            self::Last30 => 'Last 30',
            self::Last30Days => 'Last 30 days',
            self::Last90Days => 'Last 90 days',
        };
    }

    public function itemLimit(): ?int
    {
        return match ($this) {
            self::Last10 => 10,
            self::Last20 => 20,
            self::Last30 => 30,
            default => null,
        };
    }

    public function days(): ?int
    {
        return match ($this) {
            self::Last30Days => 30,
            self::Last90Days => 90,
            default => null,
        };
    }

    /** The window shown on cards and used for marketplace filtering. */
    public static function default(): self
    {
        return self::Last20;
    }
}
