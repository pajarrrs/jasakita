<?php

namespace App\Enums;

enum RequestMode: string
{
    case Quote = 'quote';
    case Direct = 'direct';

    public function label(): string
    {
        return match ($this) {
            self::Quote => 'Minta Penawaran',
            self::Direct => 'Pesan Langsung',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Quote => 'Mitra mengirim penawaran harga, kamu pilih yang paling cocok.',
            self::Direct => 'Harga mengikuti patokan, mitra tercepat langsung mengambil job.',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Quote => 'zinc',
            self::Direct => 'teal',
        };
    }

    public function isDirect(): bool
    {
        return $this === self::Direct;
    }
}
