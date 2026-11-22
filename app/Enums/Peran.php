<?php

namespace App\Enums;

enum Peran: string
{
    case Admin = 'admin';
    case Resepsionis = 'resepsionis';
    case Tamu = 'tamu';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Resepsionis => 'Resepsionis',
            self::Tamu => 'Tamu',
        };
    }

    /** Peran yang termasuk staf (bisa akses panel admin) */
    public static function staf(): array
    {
        return [self::Admin, self::Resepsionis];
    }
}
