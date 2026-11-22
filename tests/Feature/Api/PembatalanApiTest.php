<?php

use App\Enums\StatusPembatalan;
use App\Enums\StatusReservasi;
use App\Models\Pembatalan;
use App\Models\Reservasi;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tamuDenganReservasi(StatusReservasi $status = StatusReservasi::Dikonfirmasi): array
{
    $user = User::factory()->create();
    $r = Reservasi::factory()->create(['tamu_id' => Tamu::factory()->create(['user_id' => $user->id])->id, 'status' => $status]);

    return [$user, $r];
}

it('tamu hanya melihat riwayat reservasinya sendiri', function () {
    [$user, $r] = tamuDenganReservasi();
    Reservasi::factory()->create();

    $this->actingAs($user)->getJson('/api/v1/saya/reservasi')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $r->id);

    $this->actingAs(User::factory()->admin()->create())->getJson('/api/v1/saya/reservasi')->assertForbidden();
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/saya/reservasi')->assertUnauthorized();
});

it('tamu mengajukan pembatalan, petugas menyetujui sehingga reservasi batal', function () {
    [$user, $r] = tamuDenganReservasi();

    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembatalan", ['alasan' => 'Jadwal berubah'])
        ->assertCreated()->assertJsonPath('data.status.value', 'diajukan');
    expect($r->fresh()->status)->toBe(StatusReservasi::Dikonfirmasi);

    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembatalan", ['alasan' => 'Jadwal berubah'])->assertUnprocessable();

    $staf = User::factory()->resepsionis()->create();
    $p = Pembatalan::first();
    $this->actingAs($staf)->getJson('/api/v1/pembatalan?status=diajukan')->assertJsonCount(1, 'data');
    $this->actingAs($staf)->postJson("/api/v1/pembatalan/{$p->id}/proses", ['status' => 'disetujui'])
        ->assertOk()->assertJsonPath('data.status.value', 'disetujui');

    expect($r->fresh()->status)->toBe(StatusReservasi::Batal);
    expect($p->fresh()->diproses_oleh)->toBe($staf->id);
    $this->actingAs($staf)->postJson("/api/v1/pembatalan/{$p->id}/proses", ['status' => 'ditolak', 'catatan_petugas' => 'x'])->assertUnprocessable();
});

it('penolakan pembatalan membiarkan reservasi tetap berjalan dan wajib beralasan', function () {
    [$user, $r] = tamuDenganReservasi(StatusReservasi::Menunggu);
    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembatalan", ['alasan' => 'Salah tanggal'])->assertCreated();
    $p = Pembatalan::first();
    $staf = User::factory()->admin()->create();

    $this->actingAs($staf)->postJson("/api/v1/pembatalan/{$p->id}/proses", ['status' => 'ditolak'])->assertUnprocessable();
    $this->actingAs($staf)->postJson("/api/v1/pembatalan/{$p->id}/proses", ['status' => 'ditolak', 'catatan_petugas' => 'Sudah lewat batas'])->assertOk();

    expect($r->fresh()->status)->toBe(StatusReservasi::Menunggu);
    expect($p->fresh()->status)->toBe(StatusPembatalan::Ditolak);
});

it('pembatalan hanya untuk reservasi sendiri yang belum check-in', function () {
    [$user, $checkin] = tamuDenganReservasi(StatusReservasi::Checkin);
    $lain = Reservasi::factory()->create();

    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$checkin->id}/pembatalan", ['alasan' => 'Ingin batal'])->assertUnprocessable();
    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$lain->id}/pembatalan", ['alasan' => 'Ingin batal'])->assertNotFound();
    expect(Pembatalan::count())->toBe(0);
});

it('pembatalan langsung oleh petugas memakai pengajuan yang ada, tanpa catatan ganda', function () {
    [$user, $r] = tamuDenganReservasi();
    $this->actingAs($user)->postJson("/api/v1/saya/reservasi/{$r->id}/pembatalan", ['alasan' => 'Jadwal berubah'])->assertCreated();

    $staf = User::factory()->resepsionis()->create();
    $this->actingAs($staf)->postJson("/api/v1/reservasi/{$r->id}/status", ['status' => 'batal', 'alasan' => 'Jadwal berubah'])->assertOk();

    expect(Pembatalan::count())->toBe(1);
    expect(Pembatalan::first()->status)->toBe(StatusPembatalan::Disetujui);
});
