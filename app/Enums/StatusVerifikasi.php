<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

/**
 * Status verifikasi satu pembayaran (berbeda dengan StatusPembayaran milik reservasi).
 */
enum StatusVerifikasi: string
{
    use SebagaiOpsi;

    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Diterima = 'diterima';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MenungguVerifikasi => 'Menunggu verifikasi',
            self::Diterima => 'Diterima',
            self::Ditolak => 'Ditolak',
        };
    }
}
