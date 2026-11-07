<?php

namespace Database\Seeders;

use App\Enums\StatusPembayaran;
use App\Enums\StatusReservasi;
use App\Enums\SumberReservasi;
use App\Models\CheckinCheckout;
use App\Models\Kamar;
use App\Models\Reservasi;
use App\Models\Tamu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReservasiSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Kode booking diisi manual di sini karena DatabaseSeeder memakai WithoutModelEvents,
     * sehingga pembuatan kode otomatis di model tidak berjalan saat seeding.
     */
    public function run(): void
    {
        if (Reservasi::query()->exists()) {
            return;
        }

        $this->call([KamarSeeder::class, TamuSeeder::class]);

        $hariIni = Carbon::today();

        $contoh = [
            ['BK-0001', '3374011234560001', '101', -1, 3, StatusReservasi::Checkin, StatusPembayaran::Lunas, SumberReservasi::Online],
            ['BK-0002', '3374011234560002', '201', 3, 2, StatusReservasi::Dikonfirmasi, StatusPembayaran::Dp, SumberReservasi::Online],
            ['BK-0003', '3372011234560003', '401', 7, 2, StatusReservasi::Menunggu, StatusPembayaran::BelumBayar, SumberReservasi::WalkIn],
            ['BK-0004', '3372011234560004', '102', -10, 3, StatusReservasi::Selesai, StatusPembayaran::Lunas, SumberReservasi::Online],
        ];

        foreach ($contoh as [$kode, $nik, $nomorKamar, $mulaiDalamHari, $malam, $status, $statusPembayaran, $sumber]) {
            $reservasi = Reservasi::query()->create([
                'kode_booking' => $kode,
                'tamu_id' => Tamu::query()->where('nik', $nik)->value('id'),
                'tgl_checkin' => $hariIni->copy()->addDays($mulaiDalamHari),
                'tgl_checkout' => $hariIni->copy()->addDays($mulaiDalamHari + $malam),
                'status_pembayaran' => $statusPembayaran,
                'sumber' => $sumber,
                'status' => $status,
            ]);

            $reservasi->tambahKamar(
                Kamar::query()->with('tipeKamar')->where('nomor_kamar', $nomorKamar)->firstOrFail(),
            );
            $reservasi->hitungUlangTotal();

            if (in_array($status, [StatusReservasi::Checkin, StatusReservasi::Selesai], true)) {
                CheckinCheckout::query()->create([
                    'reservasi_id' => $reservasi->getKey(),
                    'waktu_checkin' => $reservasi->tgl_checkin->copy()->setTime(14, 0),
                    'waktu_checkout' => $status === StatusReservasi::Selesai
                        ? $reservasi->tgl_checkout->copy()->setTime(12, 0)
                        : null,
                ]);
            }
        }
    }
}
