<?php

namespace Database\Seeders;

use App\Models\Tamu;
use Illuminate\Database\Seeder;

class TamuSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $daftarTamu = [
            ['nama' => 'Budi Santoso', 'nik' => '3374011234560001', 'no_hp' => '081234567801', 'alamat' => 'Jl. Pandanaran No. 12, Semarang'],
            ['nama' => 'Siti Aminah', 'nik' => '3374011234560002', 'no_hp' => '081234567802', 'alamat' => 'Jl. Pemuda No. 45, Semarang'],
            ['nama' => 'Agus Prasetyo', 'nik' => '3372011234560003', 'no_hp' => '081234567803', 'alamat' => 'Jl. Slamet Riyadi No. 8, Surakarta'],
            ['nama' => 'Dewi Lestari', 'nik' => '3372011234560004', 'no_hp' => '081234567804', 'alamat' => 'Jl. Ir. Sutami No. 36, Surakarta'],
            ['nama' => 'Rizki Ramadhan', 'nik' => '3471011234560005', 'no_hp' => '081234567805', 'alamat' => 'Jl. Malioboro No. 20, Yogyakarta'],
            ['nama' => 'Nur Hidayah', 'nik' => '3578011234560006', 'no_hp' => '081234567806', 'alamat' => 'Jl. Tunjungan No. 5, Surabaya'],
        ];

        foreach ($daftarTamu as $tamu) {
            Tamu::query()->firstOrCreate(['nik' => $tamu['nik']], $tamu);
        }
    }
}
