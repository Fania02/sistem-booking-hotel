<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ---- Akun bawaan ----
        User::query()->firstOrCreate(
            ['email' => 'admin@royalhotel.com'],
            ['name' => 'Admin Hotel', 'password' => 'password123', 'role' => Peran::Admin]
        );

        User::query()->firstOrCreate(
            ['email' => 'resepsionis@royalhotel.com'],
            ['name' => 'Resepsionis', 'password' => 'password123', 'role' => Peran::Resepsionis]
        );

        User::query()->firstOrCreate(
            ['email' => 'tamu@example.com'],
            ['name' => 'Tamu Demo', 'password' => 'password123', 'role' => Peran::Tamu]
        );

        // Akun lama (backward compat)
        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password', 'role' => Peran::Tamu]
        );

        // ---- Fasilitas dasar ----
        $fasilitasDasar = ['Wi-Fi', 'AC', 'TV', 'Kolam Renang', 'Spa', 'Sarapan', 'Parkir', 'Resto'];
        foreach ($fasilitasDasar as $nama) {
            DB::table('fasilitas')->insertOrIgnore([
                'nama'       => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->call([
            TipeKamarSeeder::class,
            KamarSeeder::class,
            TamuSeeder::class,
            ReservasiSeeder::class,
        ]);
    }
}
