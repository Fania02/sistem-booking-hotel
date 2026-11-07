<?php

namespace App\Enums;

enum StatusReservasi: string
{
    case Menunggu = 'menunggu';
    case Dikonfirmasi = 'dikonfirmasi';
    case Checkin = 'checkin';
    case Selesai = 'selesai';
    case Batal = 'batal';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Dikonfirmasi => 'Dikonfirmasi',
            self::Checkin => 'Check-in',
            self::Selesai => 'Selesai',
            self::Batal => 'Batal',
        };
    }

    /**
     * Status reservasi yang masih memakai kamar (kamar tidak boleh dipesan orang lain).
     *
     * @return list<self>
     */
    public static function aktif(): array
    {
        return [self::Menunggu, self::Dikonfirmasi, self::Checkin];
    }
}
