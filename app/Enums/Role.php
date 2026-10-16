<?php

namespace App\Enums;

enum Role: string
{
    case Customer = 'customer';
    case Partner = 'partner';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Pelanggan',
            self::Partner => 'Mitra',
            self::Admin => 'Admin',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Customer => 'zinc',
            self::Partner => 'teal',
            self::Admin => 'violet',
        };
    }

    public function isCustomer(): bool
    {
        return $this === self::Customer;
    }

    public function isPartner(): bool
    {
        return $this === self::Partner;
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
