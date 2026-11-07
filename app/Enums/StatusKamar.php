<?php

namespace App\Enums;

enum StatusKamar: string
{
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
