<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu review',
            self::Approved => 'Terverifikasi',
            self::Rejected => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Approved => 'emerald',
            self::Rejected => 'rose',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'clock',
            self::Approved => 'check-badge',
            self::Rejected => 'x-circle',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Pending => 'Data verifikasi sedang diperiksa admin.',
            self::Approved => 'Identitasmu sudah diverifikasi. Badge terverifikasi tampil di penawaranmu.',
            self::Rejected => 'Verifikasi ditolak. Perbaiki data lalu kirim ulang.',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Pending;
    }

    public function isApproved(): bool
    {
        return $this === self::Approved;
    }

    public function isRejected(): bool
    {
        return $this === self::Rejected;
    }
}
