<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum StatusReservasi: string
{
    use SebagaiOpsi;

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

    /**
     * Nilai (string) dari status aktif, siap dipakai di query.
     *
     * @return list<string>
     */
    public static function nilaiAktif(): array
    {
        return array_map(fn (self $status): string => $status->value, self::aktif());
    }

    /**
     * Status tujuan yang boleh dipilih dari status ini.
     *
     * @return list<self>
     */
    public function transisiTersedia(): array
    {
        return match ($this) {
            self::Menunggu => [self::Dikonfirmasi, self::Batal],
            self::Dikonfirmasi => [self::Checkin, self::Batal],
            self::Checkin => [self::Selesai],
            self::Selesai, self::Batal => [],
        };
    }

    public function bolehBerubahKe(self $tujuan): bool
    {
        return in_array($tujuan, $this->transisiTersedia(), true);
    }

    /**
     * Data reservasi (tanggal, kamar, tamu) hanya boleh diubah selama reservasi masih aktif.
     */
    public function bisaDiubah(): bool
    {
        return in_array($this, self::aktif(), true);
    }
}
