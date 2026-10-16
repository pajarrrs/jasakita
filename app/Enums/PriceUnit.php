<?php

namespace App\Enums;

enum PriceUnit: string
{
    case PerJob = 'per_job';
    case PerHour = 'per_hour';
    case PerDay = 'per_day';

    public function label(): string
    {
        return match ($this) {
            self::PerJob => 'per pekerjaan',
            self::PerHour => 'per jam',
            self::PerDay => 'per hari',
        };
    }
}
