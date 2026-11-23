<?php

use App\Enums\Peran;
use App\Http\Controllers\Api\V1\AdminDashboardController;
use App\Http\Controllers\Api\V1\AdminPembatalanController;
use App\Http\Controllers\Api\V1\AdminPembayaranController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\PenggunaController;
use App\Http\Controllers\Api\V1\TamuReservasiController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('beranda');

Route::middleware('guest')->group(function (): void {
    Route::view('masuk', 'auth.masuk')->name('login');
    Route::view('daftar', 'auth.daftar')->name('daftar');
});

/*
| Endpoint API berbasis sesi login (cookie).
*/
Route::prefix('api/v1')->name('api.v1.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('auth.login');

    Route::post('auth/registrasi', [AuthController::class, 'registrasi'])
        ->middleware('throttle:10,1')
        ->name('auth.registrasi');

    Route::post('auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::post('booking', [BookingController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('booking');
});

/*
| API milik tamu yang sedang login.
*/
Route::prefix('api/v1/saya')
    ->name('api.v1.saya.')
    ->middleware('auth')
    ->group(function (): void {
        Route::get('reservasi', [TamuReservasiController::class, 'index'])
            ->name('reservasi.index');

        Route::post('reservasi/{reservasi}/pembayaran', [TamuReservasiController::class, 'bayar'])
            ->name('reservasi.bayar');

        Route::post('reservasi/{reservasi}/pembatalan', [TamuReservasiController::class, 'batal'])
            ->name('reservasi.batal');
    });

/*
| API panel admin: hanya untuk petugas.
*/
Route::prefix('api/v1/admin')
    ->name('api.v1.admin.')
    ->middleware(['auth', 'peran:admin,resepsionis'])
    ->group(function (): void {
        Route::get('ringkasan', AdminDashboardController::class)
            ->name('ringkasan');

        Route::get('pembayaran', [AdminPembayaranController::class, 'index'])
            ->name('pembayaran.index');

        Route::post('pembayaran/{pembayaran}/verifikasi', [AdminPembayaranController::class, 'verifikasi'])
            ->name('pembayaran.verifikasi');

        Route::get('pembatalan', [AdminPembatalanController::class, 'index'])
            ->name('pembatalan.index');

        Route::post('pembatalan/{pembatalan}/proses', [AdminPembatalanController::class, 'proses'])
            ->name('pembatalan.proses');

        Route::middleware('peran:admin')->group(function (): void {
            Route::apiResource('pengguna', PenggunaController::class)
                ->except('show')
                ->parameters(['pengguna' => 'pengguna']);
        });
    });

/*
| Halaman tamu.
*/
Route::prefix('tamu')
    ->name('tamu.')
    ->middleware(['auth'])
    ->group(function (): void {
        Route::view('dashboard', 'tamu.dashboard')->name('dashboard');
    });

/*
| Halaman admin.
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware([
        'auth',
        'peran:'.implode(',', array_map(fn (Peran $p) => $p->value, Peran::staf())),
    ])
    ->group(function (): void {
        Route::view('dashboard', 'admin.dashboard')->name('dashboard');
        Route::view('reservasi', 'admin.reservasi')->name('reservasi');
        Route::view('tamu', 'admin.tamu')->name('tamu');
        Route::view('pembayaran', 'admin.pembayaran')->name('pembayaran');
        Route::view('pembatalan', 'admin.pembatalan')->name('pembatalan');
    });

// Kelola akun: khusus admin.
Route::view('admin/pengguna', 'admin.pengguna')
    ->middleware(['auth', 'peran:admin'])
    ->name('admin.pengguna');