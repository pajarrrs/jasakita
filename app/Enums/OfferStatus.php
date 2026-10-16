<?php

namespace App\Enums;

enum OfferStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu jawaban',
            self::Accepted => 'Diterima',
            self::Rejected => 'Ditolak',
            self::Withdrawn => 'Ditarik',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'zinc',
            self::Accepted => 'emerald',
            self::Rejected => 'rose',
            self::Withdrawn => 'zinc',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
