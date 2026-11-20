<?php

use App\Enums\Peran;
use App\Models\Tamu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function dataRegistrasi(array $override = []): array
{
    return [
        'name' => 'Sari Wulan',
        'email' => 'sari@example.test',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'nik' => '3372010101010001',
        'no_hp' => '081234567890',
        'alamat' => 'Solo',
        ...$override,
    ];
}

it('mendaftarkan akun tamu, membuat data tamu, dan langsung login', function () {
    $this->postJson('/api/v1/auth/registrasi', dataRegistrasi())
        ->assertCreated()
        ->assertJsonPath('data.peran.value', 'tamu')
        ->assertJsonPath('data.staf', false);

    $user = User::where('email', 'sari@example.test')->first();
    expect($user->role)->toBe(Peran::Tamu)
        ->and($user->password)->not->toBe('rahasia123')
        ->and($user->tamu->nik)->toBe('3372010101010001');

    $this->app['auth']->forgetGuards(); // request berikutnya memuat user dari sesi, seperti di browser
    $this->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('data.email', 'sari@example.test');
});

it('tidak membiarkan role dikirim dari form registrasi', function () {
    $this->postJson('/api/v1/auth/registrasi', dataRegistrasi(['role' => 'admin']))->assertCreated();

    expect(User::where('email', 'sari@example.test')->value('role'))->toBe(Peran::Tamu);
});

it('memvalidasi registrasi', function () {
    User::factory()->create(['email' => 'sari@example.test']);

    $this->postJson('/api/v1/auth/registrasi', dataRegistrasi([
        'password' => 'pendek', 'password_confirmation' => 'beda', 'nik' => '123',
    ]))->assertUnprocessable()->assertJsonValidationErrors(['email', 'password', 'nik']);
});

it('menghubungkan akun ke tamu lama bila NIK dan nama sama', function () {
    $tamu = Tamu::factory()->create(['nama' => 'Sari Wulan', 'nik' => '3372010101010001']);

    $this->postJson('/api/v1/auth/registrasi', dataRegistrasi())->assertCreated();

    expect($tamu->fresh()->user_id)->toBe(User::where('email', 'sari@example.test')->value('id'))
        ->and(Tamu::count())->toBe(1);
});

it('menolak NIK milik tamu dengan nama lain atau yang sudah punya akun', function () {
    Tamu::factory()->create(['nama' => 'Orang Lain', 'nik' => '3372010101010001']);
    $this->postJson('/api/v1/auth/registrasi', dataRegistrasi())->assertUnprocessable()->assertJsonValidationErrors('nik');

    Tamu::query()->update(['nama' => 'Sari Wulan', 'user_id' => User::factory()->create()->id]);
    $this->postJson('/api/v1/auth/registrasi', dataRegistrasi())->assertUnprocessable()->assertJsonValidationErrors('nik');
    expect(User::where('email', 'sari@example.test')->exists())->toBeFalse();
});

it('login dengan email dan password yang benar', function () {
    $user = User::factory()->resepsionis()->create(['email' => 'rese@example.test', 'password' => 'password']);

    $this->postJson('/api/v1/auth/login', ['email' => 'rese@example.test', 'password' => 'password'])
        ->assertOk()->assertJsonPath('data.peran.value', 'resepsionis')->assertJsonPath('data.staf', true);

    $this->assertAuthenticatedAs($user);
});

it('menolak login yang salah dan membatasi percobaan berulang', function () {
    User::factory()->create(['email' => 'a@example.test', 'password' => 'password']);

    foreach (range(1, 5) as $i) {
        $this->postJson('/api/v1/auth/login', ['email' => 'a@example.test', 'password' => 'salah'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    $this->postJson('/api/v1/auth/login', ['email' => 'a@example.test', 'password' => 'password'])
        ->assertStatus(429);
    $this->assertGuest();
});

it('logout mengakhiri sesi', function () {
    $this->actingAs(User::factory()->create());

    $this->postJson('/api/v1/auth/logout')->assertNoContent();
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('me butuh login', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});
