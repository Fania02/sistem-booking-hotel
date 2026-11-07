<?php

namespace Database\Seeders;

use App\Enums\StatusKamar;
use App\Models\Kamar;
use App\Models\TipeKamar;
use Illuminate\Database\Seeder;

class KamarSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TipeKamarSeeder::class);

        $daftarKamar = [
            'Standard' => ['101' => 1, '102' => 1, '103' => 1, '104' => 1, '105' => 1],
            'Deluxe' => ['201' => 2, '202' => 2, '203' => 2, '204' => 2],
            'Family' => ['301' => 3, '302' => 3],
            'Suite' => ['401' => 4, '402' => 4],
        ];

        foreach ($daftarKamar as $namaTipe => $kamarPerTipe) {
            $tipeKamar = TipeKamar::query()->where('nama_tipe', $namaTipe)->firstOrFail();

            foreach ($kamarPerTipe as $nomorKamar => $lantai) {
                Kamar::query()->firstOrCreate(
                    ['nomor_kamar' => (string) $nomorKamar],
                    [
                        'tipe_kamar_id' => $tipeKamar->getKey(),
                        'lantai' => $lantai,
                        // Kamar 105 sengaja dibuat perbaikan supaya status ini ikut terlihat saat uji tampilan.
                        'status' => $nomorKamar === '105' ? StatusKamar::Perbaikan : StatusKamar::Tersedia,
                    ],
                );
            }
        }
    }
}
