<?php

use App\Enums\StatusReservasi;
use App\Models\Kamar;
use App\Models\Reservasi;
use App\Models\Tamu;
use App\Models\TipeKamar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menolak pengunjung tanpa login dari API petugas', function (string $metode, string $url) {
    $this->json($metode, $url)->assertUnauthorized();
})->with([
    ['GET', '/api/v1/kamar'],
    ['GET', '/api/v1/tamu'],
    ['GET', '/api/v1/reservasi'],
    ['POST', '/api/v1/tamu'],
    ['POST', '/api/v1/reservasi'],
    ['POST', '/api/v1/reservasi/1/status'],
    ['DELETE', '/api/v1/kamar/1'],
]);

it('menolak akun tamu dari API petugas', function (string $metode, string $url) {
    $this->actingAs(User::factory()->create())->json($metode, $url)->assertForbidden();
})->with([
    ['GET', '/api/v1/kamar'],
    ['GET', '/api/v1/tamu'],
    ['GET', '/api/v1/reservasi'],
    ['POST', '/api/v1/reservasi/1/status'],
]);

it('membuka data publik tanpa login', function () {
    Kamar::factory()->create();

    $this->getJson('/api/v1/tipe-kamar')->assertOk();
    $this->getJson('/api/v1/kamar/tersedia?tgl_checkin=2030-01-01&tgl_checkout=2030-01-02')->assertOk()->assertJsonCount(1, 'data');
});

it('mengizinkan resepsionis mengelola tamu dan reservasi tetapi tidak kamar atau penghapusan', function () {
    $this->actingAs(User::factory()->resepsionis()->create());
    $kamar = Kamar::factory()->create();
    $tamu = Tamu::factory()->create();
    $batal = Reservasi::factory()->create(['status' => StatusReservasi::Batal]);

    $this->getJson('/api/v1/kamar')->assertOk();
    $this->postJson('/api/v1/tamu', ['nama' => 'Budi', 'nik' => '3372010101010009', 'no_hp' => '0812'])->assertCreated();

    $this->postJson('/api/v1/kamar', ['tipe_kamar_id' => TipeKamar::factory()->create()->id, 'nomor_kamar' => '999', 'lantai' => 9, 'status' => 'tersedia'])->assertForbidden();
    $this->putJson("/api/v1/kamar/{$kamar->id}", [])->assertForbidden();
    $this->deleteJson("/api/v1/kamar/{$kamar->id}")->assertForbidden();
    $this->deleteJson("/api/v1/tamu/{$tamu->id}")->assertForbidden();
    $this->deleteJson("/api/v1/reservasi/{$batal->id}")->assertForbidden();
});

it('mengizinkan admin menghapus kamar, tamu, dan reservasi batal', function () {
    $this->actingAs(User::factory()->admin()->create());
    $kamar = Kamar::factory()->create();
    $tamu = Tamu::factory()->create();
    $batal = Reservasi::factory()->create(['status' => StatusReservasi::Batal]);

    $this->deleteJson("/api/v1/kamar/{$kamar->id}")->assertNoContent();
    $this->deleteJson("/api/v1/tamu/{$tamu->id}")->assertNoContent();
    $this->deleteJson("/api/v1/reservasi/{$batal->id}")->assertNoContent();
});

it('mengisi kolom petugas otomatis dari akun yang login', function () {
    $resepsionis = User::factory()->resepsionis()->create();
    $this->actingAs($resepsionis);

    $id = $this->postJson('/api/v1/reservasi', [
        'tamu_id' => Tamu::factory()->create()->id,
        'tgl_checkin' => now()->addDays(2)->toDateString(),
        'tgl_checkout' => now()->addDays(4)->toDateString(),
        'sumber' => 'walk-in',
        'kamar_ids' => [Kamar::factory()->create()->id],
        'dibuat_oleh' => 9999,
    ])->assertCreated()->json('data.id');

    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'dikonfirmasi'])->assertOk();
    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'checkin'])->assertOk();
    $this->postJson("/api/v1/reservasi/{$id}/status", ['status' => 'selesai'])->assertOk();

    $reservasi = Reservasi::with('checkinCheckout')->find($id);
    expect($reservasi->dibuat_oleh)->toBe($resepsionis->id)
        ->and($reservasi->checkinCheckout->diproses_checkin_oleh)->toBe($resepsionis->id)
        ->and($reservasi->checkinCheckout->diproses_checkout_oleh)->toBe($resepsionis->id);
});

it('mencatat petugas yang memproses pembatalan', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);
    $reservasi = Reservasi::factory()->create(['status' => StatusReservasi::Menunggu]);

    $this->postJson("/api/v1/reservasi/{$reservasi->id}/status", ['status' => 'batal', 'alasan' => 'Tamu berhalangan'])->assertOk();

    expect($reservasi->pembatalan()->first()->diproses_oleh)->toBe($admin->id);
});

it('mengarahkan pengunjung ke halaman masuk dan menolak tamu dari panel admin', function () {
    $this->get('/admin/reservasi')->assertRedirect('/masuk');
    $this->actingAs(User::factory()->create())->get('/admin/reservasi')->assertForbidden();
});

it('mengalihkan akun yang sudah login dari halaman masuk', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/masuk')->assertRedirect('/admin/reservasi');
    $this->actingAs(User::factory()->create())->get('/daftar')->assertRedirect('/');
});

it('menampilkan halaman masuk dan daftar', function () {
    $this->get('/masuk')->assertOk()->assertSee('form-masuk', false);
    $this->get('/daftar')->assertOk()->assertSee('form-daftar', false);
});
