<?php

namespace Database\Seeders;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Akun contoh (password semuanya: password)
        foreach ([
            ['Admin Hotel', 'admin@royalhotel.test', Peran::Admin],
            ['Resepsionis', 'resepsionis@royalhotel.test', Peran::Resepsionis],
            ['Tamu Contoh', 'tamu@royalhotel.test', Peran::Tamu],
        ] as [$nama, $email, $peran]) {
            $user = User::query()->firstOrNew(['email' => $email]);
            $user->name = $nama;
            $user->password = 'password';
            $user->role = $peran;
            $user->save();
        }

        $this->call([
            TipeKamarSeeder::class,
            FasilitasSeeder::class,
            KamarSeeder::class,
            TamuSeeder::class,
            ReservasiSeeder::class,
            PenggunaSeeder::class,
            TransaksiSeeder::class,
        ]);
    }
}
