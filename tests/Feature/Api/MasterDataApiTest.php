<?php

use App\Models\Fasilitas;
use App\Models\Kamar;
use App\Models\TipeKamar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->admin = User::factory()->admin()->create());

it('admin membuat tipe kamar beserta fasilitasnya, publik bisa melihat', function () {
    $wifi = Fasilitas::query()->create(['nama' => 'WiFi']);
    $ac = Fasilitas::query()->create(['nama' => 'AC']);

    $this->actingAs($this->admin)->postJson('/api/v1/tipe-kamar', [
        'nama_tipe' => 'Deluxe', 'harga_per_malam' => 500000, 'kapasitas' => 2, 'fasilitas_ids' => [$wifi->id, $ac->id],
    ])->assertCreated()->assertJsonCount(2, 'data.fasilitas');

    $id = TipeKamar::first()->id;
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/tipe-kamar')->assertOk()->assertJsonPath('data.0.fasilitas.0.nama', 'AC');
    $this->getJson("/api/v1/tipe-kamar/{$id}")->assertOk()->assertJsonPath('data.nama_tipe', 'Deluxe');
});

it('memvalidasi tipe kamar dan mengubah fasilitasnya', function () {
    $wifi = Fasilitas::query()->create(['nama' => 'WiFi']);
    $tipe = TipeKamar::factory()->create(['nama_tipe' => 'Standar']);

    $this->actingAs($this->admin)->postJson('/api/v1/tipe-kamar', ['nama_tipe' => 'Standar', 'harga_per_malam' => 1, 'kapasitas' => 1])
        ->assertUnprocessable()->assertJsonValidationErrors('nama_tipe');
    $this->actingAs($this->admin)->postJson('/api/v1/tipe-kamar', ['nama_tipe' => 'X', 'harga_per_malam' => -1, 'kapasitas' => 0, 'fasilitas_ids' => [999]])
        ->assertUnprocessable()->assertJsonValidationErrors(['harga_per_malam', 'kapasitas', 'fasilitas_ids.0']);

    $this->actingAs($this->admin)->putJson("/api/v1/tipe-kamar/{$tipe->id}", [
        'nama_tipe' => 'Standar', 'harga_per_malam' => 350000, 'kapasitas' => 3, 'fasilitas_ids' => [$wifi->id],
    ])->assertOk()->assertJsonPath('data.kapasitas', 3)->assertJsonCount(1, 'data.fasilitas');
});

it('tipe kamar yang masih dipakai kamar tidak bisa dihapus', function () {
    $dipakai = Kamar::factory()->create()->tipeKamar;
    $kosong = TipeKamar::factory()->create();

    $this->actingAs($this->admin)->deleteJson("/api/v1/tipe-kamar/{$dipakai->id}")->assertConflict();
    $this->actingAs($this->admin)->deleteJson("/api/v1/tipe-kamar/{$kosong->id}")->assertNoContent();
    expect(TipeKamar::find($kosong->id))->toBeNull();
});

it('admin mengelola fasilitas; hapus melepasnya dari tipe kamar', function () {
    $this->actingAs($this->admin)->postJson('/api/v1/fasilitas', ['nama' => 'Kolam'])->assertCreated();
    $this->actingAs($this->admin)->postJson('/api/v1/fasilitas', ['nama' => 'Kolam'])->assertUnprocessable();

    $f = Fasilitas::first();
    $this->actingAs($this->admin)->putJson("/api/v1/fasilitas/{$f->id}", ['nama' => 'Kolam Renang'])->assertOk();

    $tipe = TipeKamar::factory()->create();
    $tipe->fasilitas()->attach($f->id);
    $this->actingAs($this->admin)->getJson('/api/v1/fasilitas')->assertJsonPath('data.0.jumlah_tipe_kamar', 1);
    $this->actingAs($this->admin)->deleteJson("/api/v1/fasilitas/{$f->id}")->assertNoContent();
    expect($tipe->fasilitas()->count())->toBe(0);
});

it('resepsionis dan tamu tidak boleh mengubah data master', function (string $metode, string $url) {
    foreach ([User::factory()->resepsionis()->create(), User::factory()->create()] as $user) {
        $this->actingAs($user)->json($metode, $url, ['nama' => 'x'])->assertForbidden();
    }
    $this->app['auth']->forgetGuards();
    $this->json($metode, $url)->assertUnauthorized();
})->with([
    ['POST', '/api/v1/tipe-kamar'],
    ['PUT', '/api/v1/tipe-kamar/1'],
    ['DELETE', '/api/v1/tipe-kamar/1'],
    ['POST', '/api/v1/fasilitas'],
    ['DELETE', '/api/v1/fasilitas/1'],
    ['GET', '/api/v1/pengguna'],
    ['POST', '/api/v1/pengguna'],
    ['DELETE', '/api/v1/pengguna/1'],
]);

it('admin mengelola akun petugas', function () {
    $this->actingAs($this->admin)->postJson('/api/v1/pengguna', [
        'name' => 'Sari', 'email' => 'sari@hotel.test', 'password' => 'rahasia123', 'peran' => 'resepsionis',
    ])->assertCreated()->assertJsonPath('data.peran.value', 'resepsionis');

    $sari = User::where('email', 'sari@hotel.test')->first();
    $this->app['auth']->forgetGuards();
    $this->postJson('/api/v1/auth/login', ['email' => 'sari@hotel.test', 'password' => 'rahasia123'])->assertOk();

    $this->actingAs($this->admin)->putJson("/api/v1/pengguna/{$sari->id}", ['name' => 'Sari B', 'email' => 'sari@hotel.test', 'peran' => 'admin'])
        ->assertOk()->assertJsonPath('data.peran.value', 'admin');
    expect(password_verify('rahasia123', $sari->fresh()->password))->toBeTrue();

    $this->actingAs($this->admin)->getJson('/api/v1/pengguna')->assertJsonCount(2, 'data');
    $this->actingAs($this->admin)->deleteJson("/api/v1/pengguna/{$sari->id}")->assertNoContent();
});

it('akun petugas: peran tamu ditolak, akun tamu tidak terjangkau, tidak boleh hapus/turunkan diri sendiri', function () {
    $tamu = User::factory()->create();

    $this->actingAs($this->admin)->postJson('/api/v1/pengguna', ['name' => 'A', 'email' => 'a@x.test', 'password' => 'rahasia123', 'peran' => 'tamu'])
        ->assertUnprocessable()->assertJsonValidationErrors('peran');
    $this->actingAs($this->admin)->getJson("/api/v1/pengguna/{$tamu->id}")->assertNotFound();
    $this->actingAs($this->admin)->deleteJson("/api/v1/pengguna/{$tamu->id}")->assertNotFound();

    $this->actingAs($this->admin)->deleteJson("/api/v1/pengguna/{$this->admin->id}")->assertConflict();
    $this->actingAs($this->admin)->putJson("/api/v1/pengguna/{$this->admin->id}", ['name' => 'A', 'email' => $this->admin->email, 'peran' => 'resepsionis'])
        ->assertUnprocessable();
});

it('minimal satu admin harus tersisa', function () {
    $lain = User::factory()->admin()->create();
    $this->actingAs($lain)->deleteJson("/api/v1/pengguna/{$this->admin->id}")->assertNoContent();

    $resepsionis = User::factory()->resepsionis()->create();
    $this->actingAs($lain)->deleteJson("/api/v1/pengguna/{$resepsionis->id}")->assertNoContent();
    expect(User::where('role', 'admin')->count())->toBe(1);
});
