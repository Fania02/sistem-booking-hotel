<?php

namespace Database\Seeders;

use App\Models\Tamu;
use App\Models\User;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    /**
     * Membuat akun admin, resepsionis, dan satu akun tamu yang terhubung ke data tamu.
     * Semua akun memakai password: password
     *
     * Role diisi lewat forceFill karena kolom role sengaja tidak dimasukkan ke Fillable
     * (agar tidak bisa diisi dari form pendaftaran).
     */
    public function run(): void
    {
        $this->call(TamuSeeder::class);

        $daftarAkun = [
            ['name' => 'Admin Royal Hotel', 'email' => 'admin@royalhotel.test', 'role' => 'admin'],
            ['name' => 'Resepsionis Royal Hotel', 'email' => 'resepsionis@royalhotel.test', 'role' => 'resepsionis'],
            ['name' => 'Budi Santoso', 'email' => 'budi@royalhotel.test', 'role' => 'tamu'],
        ];

        foreach ($daftarAkun as $akun) {
            $user = User::query()->firstOrNew(['email' => $akun['email']]);
            $user->forceFill([
                'name' => $akun['name'],
                'password' => 'password',
                'role' => $akun['role'],
            ])->save();
        }

        // Akun budi@royalhotel.test dihubungkan ke data tamu Budi Santoso.
        Tamu::query()
            ->where('nik', '3374011234560001')
            ->whereNull('user_id')
            ->update(['user_id' => User::query()->where('email', 'budi@royalhotel.test')->value('id')]);
    }
}
