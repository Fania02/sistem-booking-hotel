<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum Peran: string
{
    use SebagaiOpsi;

    case Admin = 'admin';
    case Resepsionis = 'resepsionis';
    case Tamu = 'tamu';

    /** Peran yang boleh masuk panel admin. */
    public static function staf(): array
    {
        return [self::Admin, self::Resepsionis];
    }

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
     */
    public function adalahStaf(): bool
    {
        return in_array($this, self::staf(), true);
    }
}