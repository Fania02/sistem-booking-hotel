<?php

use App\Enums\StatusReservasi;
use App\Models\Kamar;
use App\Models\Reservasi;
use App\Models\Tamu;
use App\Models\TipeKamar;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat, melihat, mengubah, dan menghapus kamar', function () {
    $tipe = TipeKamar::factory()->create();

    $id = $this->postJson('/api/v1/kamar', [
        'tipe_kamar_id' => $tipe->id, 'nomor_kamar' => '101', 'lantai' => 1, 'status' => 'tersedia',
    ])->assertCreated()
        ->assertJsonPath('data.nomor_kamar', '101')
        ->assertJsonPath('data.status.value', 'tersedia')
        ->json('data.id');

    $this->getJson("/api/v1/kamar/{$id}")->assertOk()->assertJsonPath('data.tipe_kamar.id', $tipe->id);

    $this->putJson("/api/v1/kamar/{$id}", [
        'tipe_kamar_id' => $tipe->id, 'nomor_kamar' => '101', 'lantai' => 1, 'status' => 'perbaikan',
    ])->assertOk()->assertJsonPath('data.status.label', 'Perbaikan');

    $this->deleteJson("/api/v1/kamar/{$id}")->assertNoContent();
    $this->getJson("/api/v1/kamar/{$id}")->assertNotFound();
});

it('menolak nomor kamar ganda dan tipe yang tidak ada', function () {
    $kamar = Kamar::factory()->create(['nomor_kamar' => '201']);

    $this->postJson('/api/v1/kamar', [
        'tipe_kamar_id' => 9999, 'nomor_kamar' => '201', 'lantai' => 2, 'status' => 'tersedia',
    ])->assertUnprocessable()->assertJsonValidationErrors(['tipe_kamar_id', 'nomor_kamar']);

    // mengubah dirinya sendiri dengan nomor yang sama tidak dianggap ganda
    $this->putJson("/api/v1/kamar/{$kamar->id}", [
        'tipe_kamar_id' => $kamar->tipe_kamar_id, 'nomor_kamar' => '201', 'lantai' => 2, 'status' => 'tersedia',
    ])->assertOk();
});

it('memfilter daftar kamar', function () {
    Kamar::factory()->create(['nomor_kamar' => '301']);
    Kamar::factory()->perbaikan()->create(['nomor_kamar' => '302']);

    $this->getJson('/api/v1/kamar?status=perbaikan')->assertOk()->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nomor_kamar', '302');
    $this->getJson('/api/v1/kamar?search=301')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/kamar?status=ngawur')->assertUnprocessable();
});

it('hanya menampilkan kamar yang kosong pada tanggal tertentu', function () {
    $bebas = Kamar::factory()->create(['nomor_kamar' => '401']);
    $terpakai = Kamar::factory()->create(['nomor_kamar' => '402']);
    Kamar::factory()->perbaikan()->create(['nomor_kamar' => '403']);

    $reservasi = Reservasi::factory()->create([
        'tgl_checkin' => '2030-05-10', 'tgl_checkout' => '2030-05-12', 'status' => StatusReservasi::Dikonfirmasi,
    ]);
    $reservasi->tambahKamar($terpakai);

    $this->getJson('/api/v1/kamar/tersedia?tgl_checkin=2030-05-11&tgl_checkout=2030-05-13')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $bebas->id);

    // menginap beruntun (checkin tepat di hari checkout) diperbolehkan
    $this->getJson('/api/v1/kamar/tersedia?tgl_checkin=2030-05-12&tgl_checkout=2030-05-13')
        ->assertJsonCount(2, 'data');

    $this->getJson('/api/v1/kamar/tersedia?tgl_checkin=2030-05-13&tgl_checkout=2030-05-12')->assertUnprocessable();
});

it('menolak menghapus kamar yang dipakai reservasi aktif', function () {
    $kamar = Kamar::factory()->create();
    Reservasi::factory()->create(['status' => StatusReservasi::Menunggu])->tambahKamar($kamar);

    $this->deleteJson("/api/v1/kamar/{$kamar->id}")->assertConflict();
    expect($kamar->fresh())->not->toBeNull();
});

it('mengelola tamu dan memvalidasi NIK', function () {
    $payload = ['nama' => 'Budi', 'nik' => '3372010101010001', 'no_hp' => '08123456789', 'alamat' => 'Solo'];

    $id = $this->postJson('/api/v1/tamu', $payload)->assertCreated()->json('data.id');

    $this->postJson('/api/v1/tamu', $payload)->assertUnprocessable()->assertJsonValidationErrors('nik');
    $this->postJson('/api/v1/tamu', [...$payload, 'nik' => '123'])->assertUnprocessable()->assertJsonValidationErrors('nik');

    $this->putJson("/api/v1/tamu/{$id}", [...$payload, 'nama' => 'Budi Santoso'])
        ->assertOk()->assertJsonPath('data.nama', 'Budi Santoso');

    $this->getJson('/api/v1/tamu?search=Santoso')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/tamu?search=3372010101010001')->assertJsonCount(1, 'data');

    $this->deleteJson("/api/v1/tamu/{$id}")->assertNoContent();
    expect(Tamu::query()->count())->toBe(0);
});

it('menolak menghapus tamu yang masih punya reservasi aktif', function () {
    $tamu = Tamu::factory()->create();
    Reservasi::factory()->create(['tamu_id' => $tamu->id, 'status' => StatusReservasi::Dikonfirmasi]);

    $this->deleteJson("/api/v1/tamu/{$tamu->id}")->assertConflict();
});
