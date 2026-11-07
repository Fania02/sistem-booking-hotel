<?php

namespace Database\Seeders;

use App\Models\TipeKamar;
use Illuminate\Database\Seeder;

class TipeKamarSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $daftarTipe = [
            ['nama_tipe' => 'Standard', 'harga_per_malam' => 350000, 'kapasitas' => 2, 'deskripsi' => 'Kamar nyaman dengan satu tempat tidur double, AC, dan TV.'],
            ['nama_tipe' => 'Deluxe', 'harga_per_malam' => 550000, 'kapasitas' => 2, 'deskripsi' => 'Kamar lebih luas dengan balkon dan pemandangan kota.'],
            ['nama_tipe' => 'Family', 'harga_per_malam' => 750000, 'kapasitas' => 4, 'deskripsi' => 'Dua tempat tidur untuk keluarga, dilengkapi ruang duduk.'],
            ['nama_tipe' => 'Suite', 'harga_per_malam' => 950000, 'kapasitas' => 4, 'deskripsi' => 'Suite dengan ruang tamu terpisah dan bathtub.'],
        ];

        foreach ($daftarTipe as $tipe) {
            TipeKamar::query()->firstOrCreate(
                ['nama_tipe' => $tipe['nama_tipe']],
                $tipe,
            );
        }
    }
}
