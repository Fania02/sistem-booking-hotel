<?php

namespace App\Enums;

enum StatusPembayaran: string
{
    case BelumBayar = 'belum_bayar';
    case Dp = 'dp';
    case Lunas = 'lunas';

    public function label(): string
    {
        return match ($this) {
            self::BelumBayar => 'Belum bayar',
            self::Dp => 'DP',
            self::Lunas => 'Lunas',
        };
    }
}
