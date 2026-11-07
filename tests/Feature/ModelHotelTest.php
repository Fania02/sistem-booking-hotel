<?php

use App\Enums\StatusKamar;
use App\Enums\StatusReservasi;
use App\Models\Kamar;
use App\Models\Reservasi;
use App\Models\Tamu;
use App\Models\TipeKamar;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buatBooking(Kamar $kamar, string $masuk, string $keluar, StatusReservasi $status = StatusReservasi::Dikonfirmasi): Reservasi
{
    $reservasi = Reservasi::factory()->create([
        'tgl_checkin' => $masuk,
        'tgl_checkout' => $keluar,
        'status' => $status,
    ]);
    $reservasi->tambahKamar($kamar);

    return $reservasi;
}

it('membuat kode booking otomatis dari id', function () {
    $pertama = Reservasi::factory()->create();
    $kedua = Reservasi::factory()->create();

    expect($pertama->kode_booking)->toBe(sprintf('BK-%04d', $pertama->id))
        ->and($kedua->kode_booking)->toBe(sprintf('BK-%04d', $kedua->id))
        ->and($pertama->fresh()->kode_booking)->not->toBe($kedua->fresh()->kode_booking);
});

it('menghitung subtotal dan total dari harga tipe kamar kali jumlah malam', function () {
    $tipe = TipeKamar::factory()->create(['harga_per_malam' => 400000]);
    $kamar = Kamar::factory()->create(['tipe_kamar_id' => $tipe->id]);
    $reservasi = Reservasi::factory()->create(['tgl_checkin' => '2026-12-01', 'tgl_checkout' => '2026-12-04']);

    $reservasi->tambahKamar($kamar);
    $reservasi->hitungUlangTotal();

    expect($reservasi->jumlahMalam())->toBe(3)
        ->and($reservasi->fresh()->total_harga)->toBe('1200000.00');
});

it('menyimpan harga saat booking sehingga tidak ikut berubah jika harga tipe diubah', function () {
    $tipe = TipeKamar::factory()->create(['harga_per_malam' => 400000]);
    $kamar = Kamar::factory()->create(['tipe_kamar_id' => $tipe->id]);
    $reservasi = Reservasi::factory()->create(['tgl_checkin' => '2026-12-01', 'tgl_checkout' => '2026-12-02']);

    $detail = $reservasi->tambahKamar($kamar);
    $tipe->update(['harga_per_malam' => 999000]);

    expect($detail->fresh()->harga_per_malam)->toBe('400000.00');
});

it('menganggap kamar tidak tersedia jika tanggalnya bentrok dengan reservasi aktif', function () {
    $kamar = Kamar::factory()->create();
    buatBooking($kamar, '2026-12-10', '2026-12-13');

    $tersedia = fn (string $masuk, string $keluar): bool => Kamar::tersedia($masuk, $keluar)->whereKey($kamar->id)->exists();

    expect($tersedia('2026-12-11', '2026-12-12'))->toBeFalse()
        ->and($tersedia('2026-12-08', '2026-12-11'))->toBeFalse()
        ->and($tersedia('2026-12-12', '2026-12-15'))->toBeFalse()
        ->and($tersedia('2026-12-09', '2026-12-14'))->toBeFalse()
        ->and($tersedia('2026-12-13', '2026-12-15'))->toBeTrue()
        ->and($tersedia('2026-12-07', '2026-12-10'))->toBeTrue();
});

it('tidak menganggap reservasi batal atau selesai memblokir kamar', function () {
    $kamar = Kamar::factory()->create();
    buatBooking($kamar, '2026-12-10', '2026-12-13', StatusReservasi::Batal);
    buatBooking($kamar, '2026-12-10', '2026-12-13', StatusReservasi::Selesai);

    expect(Kamar::tersedia('2026-12-10', '2026-12-13')->whereKey($kamar->id)->exists())->toBeTrue();
});

it('tidak menampilkan kamar yang sedang perbaikan sebagai tersedia', function () {
    $kamar = Kamar::factory()->perbaikan()->create();

    expect($kamar->status)->toBe(StatusKamar::Perbaikan)
        ->and(Kamar::tersedia('2026-12-10', '2026-12-13')->whereKey($kamar->id)->exists())->toBeFalse();
});

it('mengabaikan reservasi sendiri saat memeriksa ketersediaan untuk perubahan tanggal', function () {
    $kamar = Kamar::factory()->create();
    $reservasi = buatBooking($kamar, '2026-12-10', '2026-12-13');

    expect(Kamar::tersedia('2026-12-11', '2026-12-14')->whereKey($kamar->id)->exists())->toBeFalse()
        ->and(Kamar::tersedia('2026-12-11', '2026-12-14', $reservasi->id)->whereKey($kamar->id)->exists())->toBeTrue();
});

it('menghubungkan reservasi dengan tamu dan kamar', function () {
    $tamu = Tamu::factory()->create();
    $kamar = Kamar::factory()->create();
    $reservasi = Reservasi::factory()->create(['tamu_id' => $tamu->id]);
    $reservasi->tambahKamar($kamar);

    expect($reservasi->tamu->is($tamu))->toBeTrue()
        ->and($tamu->reservasi)->toHaveCount(1)
        ->and($reservasi->kamar)->toHaveCount(1)
        ->and($kamar->reservasi->first()->is($reservasi))->toBeTrue()
        ->and($kamar->tipeKamar->kamar)->toHaveCount(1);
});

it('memfilter reservasi berdasarkan status', function () {
    Reservasi::factory()->create(['status' => StatusReservasi::Menunggu]);
    Reservasi::factory()->create(['status' => StatusReservasi::Batal]);

    expect(Reservasi::denganStatus(StatusReservasi::Menunggu)->count())->toBe(1);
});

it('memakai soft delete untuk kamar, tipe kamar, dan tamu', function () {
    $kamar = Kamar::factory()->create();
    $tamu = Tamu::factory()->create();

    $kamar->delete();
    $tamu->delete();

    expect(Kamar::count())->toBe(0)
        ->and(Kamar::withTrashed()->count())->toBe(1)
        ->and(Tamu::count())->toBe(0)
        ->and(Tamu::withTrashed()->count())->toBe(1);
});

it('mengisi data contoh lewat seeder dan aman dijalankan berulang', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(TipeKamar::count())->toBe(4)
        ->and(Kamar::count())->toBe(13)
        ->and(Tamu::count())->toBe(6)
        ->and(Reservasi::count())->toBe(4)
        ->and(Reservasi::orderBy('id')->pluck('kode_booking')->all())->toBe(['BK-0001', 'BK-0002', 'BK-0003', 'BK-0004'])
        ->and(Reservasi::where('kode_booking', 'BK-0001')->first()->total_harga)->toBe('1050000.00')
        ->and(Reservasi::where('kode_booking', 'BK-0003')->first()->total_harga)->toBe('1900000.00');
});
