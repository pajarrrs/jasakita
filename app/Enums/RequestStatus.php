<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Menunggu penawaran',
            self::Assigned => 'Mitra ditetapkan',
            self::Scheduled => 'Terjadwal',
            self::InProgress => 'Sedang dikerjakan',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'amber',
            self::Assigned => 'blue',
            self::Scheduled => 'violet',
            self::InProgress => 'teal',
            self::Completed => 'emerald',
            self::Cancelled => 'rose',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Open => 'inbox-arrow-down',
            self::Assigned => 'user-plus',
            self::Scheduled => 'calendar-days',
            self::InProgress => 'wrench-screwdriver',
            self::Completed => 'check-badge',
            self::Cancelled => 'x-circle',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled => true,
            default => false,
        };
    }

    /**
     * Pelanggan masih boleh membatalkan request pada status ini.
     */
    public function isCancellableByCustomer(): bool
    {
        return match ($this) {
            self::Open, self::Assigned, self::Scheduled => true,
            default => false,
        };
    }

    /**
     * Mitra masih boleh membatalkan job pada status ini.
     */
    public function isCancellableByPartner(): bool
    {
        return match ($this) {
            self::Assigned, self::Scheduled => true,
            default => false,
        };
    }

    /**
     * Status yang dihitung sebagai job aktif milik mitra.
     *
     * @return array<int, self>
     */
    public static function activeForPartner(): array
    {
        return [self::Assigned, self::Scheduled, self::InProgress];
    }
}
