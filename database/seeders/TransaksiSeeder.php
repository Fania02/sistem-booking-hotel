<?php

namespace Database\Seeders;

use App\Enums\StatusPembayaran;
use App\Enums\StatusReservasi;
use App\Enums\SumberReservasi;
use App\Models\CheckinCheckout;
use App\Models\Kamar;
use App\Models\Pembatalan;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransaksiSeeder extends Seeder
{
    /**
     * Melengkapi data contoh dari ReservasiSeeder: petugas yang memproses,
     * pembayaran, reservasi multi-kamar, dan pembatalan.
     */
    public function run(): void
    {
        $this->call([ReservasiSeeder::class, PenggunaSeeder::class]);

        if (Pembayaran::query()->exists()) {
            return;
        }

        $hariIni = Carbon::today();
        $resepsionisId = User::query()->where('email', 'resepsionis@royalhotel.test')->value('id');
        $adminId = User::query()->where('email', 'admin@royalhotel.test')->value('id');

        // Reservasi walk-in dibuat oleh resepsionis.
        Reservasi::query()->where('sumber', SumberReservasi::WalkIn->value)
            ->whereNull('dibuat_oleh')
            ->update(['dibuat_oleh' => $resepsionisId]);

        // Check-in dan check-out diproses oleh resepsionis.
        CheckinCheckout::query()->whereNotNull('waktu_checkin')->update(['diproses_checkin_oleh' => $resepsionisId]);
        CheckinCheckout::query()->whereNotNull('waktu_checkout')->update(['diproses_checkout_oleh' => $resepsionisId]);

        // BK-0005: reservasi yang sudah dibatalkan.
        $dibatalkan = $this->buatReservasi('BK-0005', '3471011234560005', ['202'], $hariIni->copy()->addDays(14), 2,
            StatusReservasi::Batal, StatusPembayaran::BelumBayar, SumberReservasi::Online, null);

        // BK-0006: satu reservasi berisi dua kamar (contoh detail_reservasi lebih dari satu).
        $multiKamar = $this->buatReservasi('BK-0006', '3578011234560006', ['301', '302'], $hariIni->copy()->addDays(10), 2,
            StatusReservasi::Dikonfirmasi, StatusPembayaran::Dp, SumberReservasi::Online, null);

        $this->bayar('BK-0001', 1.0, 'transfer', 'bukti/BK-0001.jpg', $hariIni->copy()->subDays(3), 'diterima', $resepsionisId, 'Lunas di muka');
        $this->bayar('BK-0002', 0.5, 'transfer', 'bukti/BK-0002.jpg', $hariIni->copy()->subDay(), 'diterima', $resepsionisId, 'DP 50%');
        $this->bayar('BK-0003', 0.5, 'qris', 'bukti/BK-0003.jpg', $hariIni, 'menunggu_verifikasi', null, 'Menunggu dicek resepsionis');
        $this->bayar('BK-0004', 0.5, 'transfer', 'bukti/BK-0004-dp.jpg', $hariIni->copy()->subDays(14), 'diterima', $resepsionisId, 'DP 50%');
        $this->bayar('BK-0004', 0.5, 'tunai', null, $hariIni->copy()->subDays(10), 'diterima', $resepsionisId, 'Pelunasan saat check-in');
        $this->bayar('BK-0006', 0.5, 'transfer', 'bukti/BK-0006.jpg', $hariIni->copy()->subDays(2), 'diterima', $adminId, 'DP 50%');

        Pembatalan::query()->create([
            'reservasi_id' => $dibatalkan->getKey(),
            'alasan' => 'Jadwal perjalanan berubah.',
            'tgl_pengajuan' => $hariIni->copy()->subDays(2)->setTime(9, 0),
            'status' => 'disetujui',
            'diproses_oleh' => $resepsionisId,
            'tgl_diproses' => $hariIni->copy()->subDays(2)->setTime(13, 0),
            'catatan_petugas' => 'Diajukan lebih dari H-3, pembatalan disetujui.',
        ]);

        // Pengajuan yang masih menunggu keputusan petugas (reservasi tetap dikonfirmasi).
        Pembatalan::query()->create([
            'reservasi_id' => $multiKamar->getKey(),
            'alasan' => 'Ingin mengganti tanggal menginap.',
            'tgl_pengajuan' => $hariIni->copy()->setTime(8, 30),
            'status' => 'diajukan',
            'diproses_oleh' => null,
            'tgl_diproses' => null,
            'catatan_petugas' => null,
        ]);
    }

    /**
     * @param  list<string>  $nomorKamar
     */
    private function buatReservasi(
        string $kode,
        string $nik,
        array $nomorKamar,
        Carbon $tglCheckin,
        int $malam,
        StatusReservasi $status,
        StatusPembayaran $statusPembayaran,
        SumberReservasi $sumber,
        ?int $petugasId,
    ): Reservasi {
        $reservasi = Reservasi::query()->firstOrCreate(['kode_booking' => $kode], [
            'tamu_id' => Tamu::query()->where('nik', $nik)->value('id'),
            'tgl_checkin' => $tglCheckin,
            'tgl_checkout' => $tglCheckin->copy()->addDays($malam),
            'status_pembayaran' => $statusPembayaran,
            'sumber' => $sumber,
            'dibuat_oleh' => $petugasId,
            'status' => $status,
        ]);

        if (! $reservasi->detail()->exists()) {
            Kamar::query()->with('tipeKamar')->whereIn('nomor_kamar', $nomorKamar)->get()
                ->each(fn (Kamar $kamar) => $reservasi->tambahKamar($kamar));
            $reservasi->hitungUlangTotal();
        }

        return $reservasi;
    }

    /**
     * Mencatat pembayaran sebesar persentase dari total harga reservasi.
     */
    private function bayar(
        string $kode,
        float $porsi,
        string $metode,
        ?string $bukti,
        Carbon $tanggal,
        string $status,
        ?int $verifikatorId,
        ?string $catatan,
    ): void {
        $reservasi = Reservasi::query()->where('kode_booking', $kode)->first();

        if ($reservasi === null) {
            return;
        }

        Pembayaran::query()->create([
            'reservasi_id' => $reservasi->getKey(),
            'jumlah_bayar' => round((float) $reservasi->total_harga * $porsi, 2),
            'metode' => $metode,
            'bukti_bayar' => $bukti,
            'tgl_bayar' => $tanggal->copy()->setTime(10, 0),
            'status' => $status,
            'diverifikasi_oleh' => $verifikatorId,
            'catatan' => $catatan,
        ]);
    }
}
