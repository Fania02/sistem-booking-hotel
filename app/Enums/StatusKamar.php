<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum StatusKamar: string
{
    use SebagaiOpsi;

    case Tersedia = 'tersedia';
    case Perbaikan = 'perbaikan';

    public function label(): string
    {
        return match ($this) {
            self::Tersedia => 'Tersedia',
            self::Perbaikan => 'Perbaikan',
        };
    }
}
