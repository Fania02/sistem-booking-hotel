<?php

use App\Enums\StatusReservasi;
use App\Models\Kamar;
use App\Models\Reservasi;
use App\Models\Tamu;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dataBooking(array $override = []): array
{
    return [
        'nama' => 'Budi Santoso',
        'nik' => '3372010101010001',
        'no_hp' => '081234567890',
        'alamat' => 'Solo',
        'tgl_checkin' => now()->addDays(3)->toDateString(),
        'tgl_checkout' => now()->addDays(5)->toDateString(),
        'kamar_ids' => [Kamar::factory()->create()->id],
        ...$override,
    ];
}

it('membuat booking tanpa login, termasuk data tamu baru', function () {
    $res = $this->postJson('/api/v1/booking', dataBooking())
        ->assertCreated()
        ->assertJsonPath('data.status.value', 'menunggu')
        ->assertJsonPath('data.jumlah_malam', 2)
        ->assertJsonCount(1, 'data.kamar');

    expect($res->json('data.kode_booking'))->toMatch('/^BK-\d{4}$/');

    $reservasi = Reservasi::first();
    expect($reservasi->sumber->value)->toBe('online')
        ->and($reservasi->dibuat_oleh)->toBeNull()
        ->and(Tamu::where('nik', '3372010101010001')->exists())->toBeTrue();
});

it('tidak membocorkan data tamu pada respons publik', function () {
    $this->postJson('/api/v1/booking', dataBooking())
        ->assertCreated()
        ->assertJsonMissingPath('data.tamu')
        ->assertJsonMissing(['nik' => '3372010101010001']);
});

it('memakai tamu lama berdasarkan NIK tanpa menimpa datanya', function () {
    $tamu = Tamu::factory()->create(['nama' => 'Budi Asli', 'nik' => '3372010101010001', 'no_hp' => '0811']);

    $this->postJson('/api/v1/booking', dataBooking(['nama' => 'Nama Lain', 'no_hp' => '0899']))->assertCreated();

    expect(Tamu::count())->toBe(1)
        ->and($tamu->fresh()->nama)->toBe('Budi Asli')
        ->and($tamu->fresh()->no_hp)->toBe('0811')
        ->and(Reservasi::first()->tamu_id)->toBe($tamu->id);
});

it('memulihkan tamu yang pernah dihapus saat NIK dipakai lagi', function () {
    $tamu = Tamu::factory()->create(['nik' => '3372010101010001']);
    $tamu->delete();

    $this->postJson('/api/v1/booking', dataBooking())->assertCreated();

    expect($tamu->fresh()->trashed())->toBeFalse();
});

it('menolak booking yang tidak valid atau bentrok', function () {
    $this->postJson('/api/v1/booking', dataBooking(['nik' => '1234']))->assertUnprocessable()->assertJsonValidationErrors('nik');
    $this->postJson('/api/v1/booking', dataBooking(['nik' => '0072010101010001']))->assertUnprocessable()->assertJsonValidationErrors('nik');
    $this->postJson('/api/v1/booking', dataBooking(['tgl_checkin' => '2020-01-01']))->assertUnprocessable()->assertJsonValidationErrors('tgl_checkin');
    $this->postJson('/api/v1/booking', dataBooking(['kamar_ids' => []]))->assertUnprocessable()->assertJsonValidationErrors('kamar_ids');

    $data = dataBooking();
    $this->postJson('/api/v1/booking', $data)->assertCreated();
    $this->postJson('/api/v1/booking', [...$data, 'nik' => '3372010101010002'])
        ->assertUnprocessable()->assertJsonValidationErrors('kamar_ids');
});

it('tidak menerima status atau total dari pengunjung', function () {
    $this->postJson('/api/v1/booking', dataBooking(['status' => 'selesai', 'total_harga' => 1, 'sumber' => 'walk-in']))->assertCreated();

    $reservasi = Reservasi::first();
    expect($reservasi->status)->toBe(StatusReservasi::Menunggu)
        ->and($reservasi->sumber->value)->toBe('online')
        ->and((float) $reservasi->total_harga)->toBeGreaterThan(1.0);
});

it('mengecek reservasi dengan kode booking dan NIK', function () {
    $kode = $this->postJson('/api/v1/booking', dataBooking())->json('data.kode_booking');

    $this->getJson("/api/v1/booking/{$kode}?nik=3372010101010001")->assertOk()->assertJsonPath('data.kode_booking', $kode);
    $this->getJson("/api/v1/booking/{$kode}?nik=3372010101010002")->assertNotFound();
    $this->getJson("/api/v1/booking/{$kode}")->assertUnprocessable();
    $this->getJson('/api/v1/booking/BK-9999?nik=3372010101010001')->assertNotFound();
});
