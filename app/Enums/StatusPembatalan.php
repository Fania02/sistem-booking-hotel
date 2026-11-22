<?php

namespace App\Enums;

enum StatusPembatalan: string
{
    case Menunggu  = 'menunggu';
    case Disetujui = 'disetujui';
    case Ditolak   = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu  => 'Menunggu',
            self::Disetujui => 'Disetujui',
            self::Ditolak   => 'Ditolak',
        };
    }
}
