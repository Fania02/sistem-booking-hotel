<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum StatusPembatalan: string
{
    use SebagaiOpsi;

    case Diajukan = 'diajukan';
    case Menunggu = 'menunggu';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::Menunggu => 'Menunggu',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
        };
    }
}