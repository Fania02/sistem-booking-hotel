<?php

use App\Models\Kamar;
use App\Models\Reservasi;
use App\Models\Tamu;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function payloadReservasi(array $override = []): array
{
    return [
        'tamu_id' => Tamu::factory()->create()->id,
        'tgl_checkin' => now()->addDays(5)->toDateString(),
        'tgl_checkout' => now()->addDays(8)->toDateString(),
        'sumber' => 'walk-in',
        'kamar_ids' => [Kamar::factory()->create()->id],
        ...$override,
    ];
}

it('membuat reservasi dengan kode booking dan total harga', function () {
    $kamar = Kamar::factory()->create();
    $harga = (float) $kamar->tipeKamar->harga_per_malam;

    $res = $this->postJson('/api/v1/reservasi', payloadReservasi(['kamar_ids' => [$kamar->id]]))
        ->assertCreated()
        ->assertJsonPath('data.status.value', 'menunggu')
        ->assertJsonPath('data.status_pembayaran.value', 'belum_bayar')
        ->assertJsonPath('data.jumlah_malam', 3)
        ->assertJsonPath('data.total_harga', (int) ($harga * 3))
        ->assertJsonCount(1, 'data.kamar');

    expect($res->json('data.kode_booking'))->toMatch('/^BK-\d{4}$/');
});

it('menolak reservasi bila kamar bentrok, sedang diperbaiki, atau tanggal tidak valid', function () {
    $kamar = Kamar::factory()->create();
    $this->postJson('/api/v1/reservasi', payloadReservasi(['kamar_ids' => [$kamar->id]]))->assertCreated();

    $this->postJson('/api/v1/reservasi', payloadReservasi(['kamar_ids' => [$kamar->id]]))
        ->assertUnprocessable()->assertJsonValidationErrors('kamar_ids');

    $rusak = Kamar::factory()->perbaikan()->create();
    $this->postJson('/api/v1/reservasi', payloadReservasi(['kamar_ids' => [$rusak->id]]))
        ->assertUnprocessable()->assertJsonValidationErrors('kamar_ids');

    $this->postJson('/api/v1/reservasi', payloadReservasi([
        'tgl_checkin' => now()->addDays(3)->toDateString(), 'tgl_checkout' => now()->addDays(3)->toDateString(),
    ]))->assertUnprocessable()->assertJsonValidationErrors('tgl_checkout');

    $this->postJson('/api/v1/reservasi', payloadReservasi(['kamar_ids' => []]))
        ->assertUnprocessable()->assertJsonValidationErrors('kamar_ids');
});

it('tidak menerima status, status_pembayaran, atau total dari request', function () {
    $this->postJson('/api/v1/reservasi', payloadReservasi([
        'status' => 'selesai', 'status_pembayaran' => 'lunas', 'total_harga' => 1,
    ]))->assertCreated()
        ->assertJsonPath('data.status.value', 'menunggu')
        ->assertJsonPath('data.status_pembayaran.value', 'belum_bayar');
});

it('mengubah tanggal dan kamar sambil mempertahankan harga lama', function () {
    $a = Kamar::factory()->create();
    $b = Kamar::factory()->create();
    $id = $this->postJson('/api/v1/reservasi', payloadReservasi(['kamar_ids' => [$a->id]]))->json('data.id');
    $hargaA = (float) $a->tipeKamar->harga_per_malam;

    $a->tipeKamar->update(['harga_per_malam' => $hargaA + 100000]);

    $res = $this->putJson("/api/v1/reservasi/{$id}", [
        'tgl_checkout' => now()->addDays(10)->toDateString(),
        'kamar_ids' => [$a->id, $b->id],
    ])->assertOk()->assertJsonPath('data.jumlah_malam', 5)->assertJsonCount(2, 'data.kamar');

    $hargaB = (float) $b->fresh()->tipeKamar->harga_per_malam;
    expect($res->json('data.total_harga'))->toEqual(($hargaA + $hargaB) * 5);
});

it('mengubah reservasi sendiri tidak dianggap bentrok dengan dirinya', function () {
    $kamar = Kamar::factory()->create();
    $id = $this->postJson('/api/v1/reservasi', payloadReservasi(['kamar_ids' => [$kamar->id]]))->json('data.id');

    $this->putJson("/api/v1/reservasi/{$id}", ['kamar_ids' => [$kamar->id]])->assertOk();
});

it('menjalankan alur status menunggu sampai selesai dan mencatat check-in/out', function () {
    $id = $this->postJson('/api/v1/reservasi', payloadReservasi())->json('data.id');

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'dikonfirmasi'])
        ->assertOk()->assertJsonPath('data.status.value', 'dikonfirmasi');

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'checkin', 'waktu' => '2030-01-01 14:00:00'])
        ->assertOk()->assertJsonPath('data.checkin_checkout.waktu_checkin', fn ($v) => str_starts_with($v, '2030-01-01T14:00'));

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'selesai'])
        ->assertOk()->assertJsonPath('data.status.value', 'selesai')
        ->assertJsonPath('data.status_berikutnya', []);

    expect(Reservasi::find($id)->checkinCheckout->waktu_checkout)->not->toBeNull();
});

it('menolak lompatan status dan perubahan dari status akhir', function () {
    $id = $this->postJson('/api/v1/reservasi', payloadReservasi())->json('data.id');

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'checkin'])
        ->assertUnprocessable()->assertJsonValidationErrors('status');
    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'ngawur'])->assertUnprocessable();

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'batal', 'alasan' => 'Berubah rencana'])->assertOk();
    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'dikonfirmasi'])->assertUnprocessable();
    $this->putJson("/api/v1/reservasi/{$id}", ['sumber' => 'online'])->assertUnprocessable();
});

it('mewajibkan alasan saat membatalkan dan membebaskan kamar', function () {
    $kamar = Kamar::factory()->create();
    $data = payloadReservasi(['kamar_ids' => [$kamar->id]]);
    $id = $this->postJson('/api/v1/reservasi', $data)->json('data.id');

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'batal'])
        ->assertUnprocessable()->assertJsonValidationErrors('alasan');

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'batal', 'alasan' => 'Sakit'])
        ->assertOk()->assertJsonPath('data.pembatalan.alasan', 'Sakit');

    // kamar kembali bisa dipesan di tanggal yang sama
    $this->postJson('/api/v1/reservasi', [...$data, 'tamu_id' => Tamu::factory()->create()->id])->assertCreated();
});

it('hanya menghapus reservasi yang sudah batal', function () {
    $id = $this->postJson('/api/v1/reservasi', payloadReservasi())->json('data.id');

    $this->deleteJson("/api/v1/reservasi/{$id}")->assertConflict();

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'batal', 'alasan' => 'Tes'])->assertOk();
    $this->deleteJson("/api/v1/reservasi/{$id}")->assertNoContent();
    $this->getJson("/api/v1/reservasi/{$id}")->assertNotFound();
});

it('memfilter dan mencari daftar reservasi', function () {
    $tamu = Tamu::factory()->create(['nama' => 'Siti Rahma']);
    $this->postJson('/api/v1/reservasi', payloadReservasi(['tamu_id' => $tamu->id]))->assertCreated();
    $this->postJson('/api/v1/reservasi', payloadReservasi())->assertCreated();

    $this->getJson('/api/v1/reservasi')->assertOk()->assertJsonCount(2, 'data')->assertJsonStructure(['data', 'links', 'meta']);
    $this->getJson('/api/v1/reservasi?search=Rahma')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/reservasi?status=menunggu')->assertJsonCount(2, 'data');
    $this->getJson('/api/v1/reservasi?status=selesai')->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/reservasi?tamu_id='.$tamu->id)->assertJsonCount(1, 'data');
});

it('menampilkan daftar tipe kamar untuk dropdown', function () {
    Kamar::factory()->create();

    $this->getJson('/api/v1/tipe-kamar')->assertOk()->assertJsonStructure(['data' => [['id', 'nama_tipe', 'harga_per_malam', 'kapasitas']]]);
});
