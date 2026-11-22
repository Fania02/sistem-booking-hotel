<?php

use App\Enums\StatusReservasi;
use App\Models\Pembatalan;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('dashboard menampilkan ringkasan; pendapatan hanya untuk admin', function () {
    $r = Reservasi::factory()->create(['status' => StatusReservasi::Dikonfirmasi, 'total_harga' => 100000, 'tgl_checkin' => now()->toDateString(), 'tgl_checkout' => now()->addDay()->toDateString()]);
    Reservasi::factory()->create(['status' => StatusReservasi::Menunggu]);
    Pembayaran::query()->create(['reservasi_id' => $r->id, 'jumlah_bayar' => 75000, 'metode' => 'tunai', 'tgl_bayar' => now(), 'status' => 'diterima']);
    Pembayaran::query()->create(['reservasi_id' => $r->id, 'jumlah_bayar' => 5000, 'metode' => 'qris', 'tgl_bayar' => now(), 'status' => 'menunggu_verifikasi']);
    Pembatalan::query()->create(['reservasi_id' => $r->id, 'alasan' => 'x', 'tgl_pengajuan' => now(), 'status' => 'diajukan']);

    $admin = $this->actingAs(User::factory()->admin()->create())->getJson('/api/v1/dashboard')->assertOk()
        ->assertJsonPath('data.reservasi.per_status.dikonfirmasi', 1)
        ->assertJsonPath('data.reservasi.per_status.menunggu', 1)
        ->assertJsonPath('data.reservasi.checkin_hari_ini', 1)
        ->assertJsonPath('data.perlu_tindakan.pembayaran_menunggu_verifikasi', 1)
        ->assertJsonPath('data.perlu_tindakan.pembatalan_diajukan', 1)
        ->assertJsonPath('data.pendapatan.hari_ini', 75000)
        ->assertJsonCount(2, 'data.reservasi_terbaru')
        ->assertJsonCount(7, 'data.grafik_kedatangan');

    $this->app['auth']->forgetGuards();
    $this->actingAs(User::factory()->resepsionis()->create())->getJson('/api/v1/dashboard')->assertOk()->assertJsonMissingPath('data.pendapatan');
});

it('dashboard tertutup untuk tamu dan pengunjung', function () {
    $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    $this->actingAs(User::factory()->create())->getJson('/api/v1/dashboard')->assertForbidden();
});
