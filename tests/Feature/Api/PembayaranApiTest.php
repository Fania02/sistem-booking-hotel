<?php

use App\Enums\StatusPembayaran;
use App\Enums\StatusReservasi;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function reservasiBayar(array $attr = []): Reservasi
{
    return Reservasi::factory()->create(['total_harga' => 1000000, 'status' => StatusReservasi::Dikonfirmasi, ...$attr]);
}

it('petugas mencatat pembayaran: langsung diterima, status DP lalu lunas', function () {
    $staf = User::factory()->resepsionis()->create();
    $r = reservasiBayar();

    $this->actingAs($staf)->postJson("/api/v1/reservasi/{$r->id}/pembayaran", ['jumlah_bayar' => 400000, 'metode' => 'tunai'])
        ->assertCreated()
        ->assertJsonPath('data.status.value', 'diterima');

    expect($r->fresh()->status_pembayaran)->toBe(StatusPembayaran::Dp);
    expect(Pembayaran::first()->diverifikasi_oleh)->toBe($staf->id);

    $this->actingAs($staf)->postJson("/api/v1/reservasi/{$r->id}/pembayaran", ['jumlah_bayar' => 600000, 'metode' => 'transfer'])->assertCreated();
    expect($r->fresh()->status_pembayaran)->toBe(StatusPembayaran::Lunas);

    $this->actingAs($staf)->getJson("/api/v1/reservasi/{$r->id}")->assertJsonPath('data.sisa_tagihan', 0);
});

it('menolak pembayaran melebihi tagihan atau pada reservasi batal', function () {
    $staf = User::factory()->admin()->create();

    $this->actingAs($staf)->postJson('/api/v1/reservasi/'.reservasiBayar()->id.'/pembayaran', ['jumlah_bayar' => 1000001, 'metode' => 'tunai'])
        ->assertUnprocessable()->assertJsonValidationErrors('jumlah_bayar');

    $batal = reservasiBayar(['status' => StatusReservasi::Batal]);
    $this->actingAs($staf)->postJson("/api/v1/reservasi/{$batal->id}/pembayaran", ['jumlah_bayar' => 1000, 'metode' => 'tunai'])
        ->assertUnprocessable()->assertJsonValidationErrors('status');
});

it('tamu mengunggah bukti: menunggu verifikasi, lalu petugas menerima', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $tamu = Tamu::factory()->create(['user_id' => $user->id]);
    $r = reservasiBayar(['tamu_id' => $tamu->id]);

    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembayaran", [
        'jumlah_bayar' => 500000, 'metode' => 'transfer', 'bukti_bayar' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertCreated()->assertJsonPath('data.status.value', 'menunggu_verifikasi');

    $p = Pembayaran::first();
    Storage::disk('public')->assertExists($p->bukti_bayar);
    expect($r->fresh()->status_pembayaran)->toBe(StatusPembayaran::BelumBayar);

    $staf = User::factory()->resepsionis()->create();
    $this->actingAs($staf)->postJson("/api/v1/pembayaran/{$p->id}/verifikasi", ['status' => 'diterima'])
        ->assertOk()->assertJsonPath('data.status.value', 'diterima');
    expect($r->fresh()->status_pembayaran)->toBe(StatusPembayaran::Dp);

    $this->actingAs($staf)->postJson("/api/v1/pembayaran/{$p->id}/verifikasi", ['status' => 'ditolak', 'catatan' => 'x'])
        ->assertUnprocessable();
});

it('penolakan pembayaran wajib beralasan dan tidak menambah saldo', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $r = reservasiBayar(['tamu_id' => Tamu::factory()->create(['user_id' => $user->id])->id]);
    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembayaran", [
        'jumlah_bayar' => 500000, 'metode' => 'qris', 'bukti_bayar' => UploadedFile::fake()->image('b.png'),
    ])->assertCreated();
    $p = Pembayaran::first();
    $staf = User::factory()->admin()->create();

    $this->actingAs($staf)->postJson("/api/v1/pembayaran/{$p->id}/verifikasi", ['status' => 'ditolak'])->assertUnprocessable();
    $this->actingAs($staf)->postJson("/api/v1/pembayaran/{$p->id}/verifikasi", ['status' => 'ditolak', 'catatan' => 'Bukti buram'])->assertOk();
    expect($r->fresh()->status_pembayaran)->toBe(StatusPembayaran::BelumBayar);

    $this->actingAs($staf)->getJson('/api/v1/pembayaran?status=ditolak')->assertJsonCount(1, 'data');
});

it('tamu wajib melampirkan bukti, tidak boleh tunai, dan tidak bisa membayar reservasi orang lain', function () {
    $user = User::factory()->create();
    $r = reservasiBayar(['tamu_id' => Tamu::factory()->create(['user_id' => $user->id])->id]);
    $lain = reservasiBayar();

    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembayaran", ['jumlah_bayar' => 1000, 'metode' => 'transfer'])
        ->assertUnprocessable()->assertJsonValidationErrors('bukti_bayar');
    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembayaran", ['jumlah_bayar' => 1000, 'metode' => 'tunai'])
        ->assertUnprocessable()->assertJsonValidationErrors('metode');

    Storage::fake('public');
    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$lain->id}/pembayaran", [
        'jumlah_bayar' => 1000, 'metode' => 'transfer', 'bukti_bayar' => UploadedFile::fake()->image('b.jpg'),
    ])->assertNotFound();
});

it('hanya petugas yang boleh melihat dan memverifikasi pembayaran', function () {
    $this->getJson('/api/v1/pembayaran')->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson('/api/v1/pembayaran')->assertForbidden();
    $this->actingAs(User::factory()->create())->postJson('/api/v1/pembayaran/1/verifikasi')->assertForbidden();
});
