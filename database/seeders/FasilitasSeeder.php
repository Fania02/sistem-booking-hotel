<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\TipeKamar;
use Illuminate\Database\Seeder;

class FasilitasSeeder extends Seeder
{
    /**
     * Mengisi daftar fasilitas lalu menghubungkannya ke setiap tipe kamar.
     */
    public function run(): void
    {
        $daftarFasilitas = ['Wi-Fi', 'AC', 'TV', 'Sarapan', 'Kolam renang', 'Spa'];

        foreach ($daftarFasilitas as $nama) {
            Fasilitas::query()->firstOrCreate(['nama' => $nama]);
        }

        $fasilitasPerTipe = [
            'Standard' => ['Wi-Fi', 'AC', 'TV'],
            'Deluxe' => ['Wi-Fi', 'AC', 'TV', 'Sarapan'],
            'Family' => ['Wi-Fi', 'AC', 'TV', 'Sarapan', 'Kolam renang'],
            'Suite' => ['Wi-Fi', 'AC', 'TV', 'Sarapan', 'Kolam renang', 'Spa'],
        ];

        foreach ($fasilitasPerTipe as $namaTipe => $namaFasilitas) {
            $tipe = TipeKamar::query()->where('nama_tipe', $namaTipe)->first();

            if ($tipe === null) {
                continue;
            }

            $tipe->fasilitas()->syncWithoutDetaching(
                Fasilitas::query()->whereIn('nama', $namaFasilitas)->pluck('id'),
            );
        }
    }
}
