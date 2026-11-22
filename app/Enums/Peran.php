<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum Peran: string
{
    use SebagaiOpsi;

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

    /**
     * Peran petugas hotel (boleh masuk panel admin).
     *
     * @return list<self>
     */
    public static function staf(): array
    {
        return [self::Admin, self::Resepsionis];
    }
}
