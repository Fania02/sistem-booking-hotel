<?php

use App\Enums\Peran;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\TamuReservasiController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('beranda');

Route::middleware('guest')->group(function (): void {
    Route::view('masuk', 'auth.masuk')->name('login');
    Route::view('daftar', 'auth.daftar')->name('daftar');
});

/*
| Endpoint yang butuh sesi login (cookie) sehingga diletakkan di web.php, tapi alamatnya
| tetap /api/v1/... supaya cocok dengan resources/js/lib/api.js.
*/
Route::prefix('api/v1')->name('api.v1.')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('auth.login');
    Route::post('auth/registrasi', [AuthController::class, 'registrasi'])->middleware('throttle:10,1')->name('auth.registrasi');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('booking', [BookingController::class, 'store'])->middleware('throttle:30,1')->name('booking');
});

/*
| API milik tamu yang sedang login (riwayat, bukti bayar, pembatalan).
*/
Route::prefix('api/v1/saya')->name('api.v1.saya.')->middleware('auth')->group(function (): void {
    Route::get('reservasi', [TamuReservasiController::class, 'index'])->name('reservasi.index');
    Route::post('reservasi/{reservasi}/pembayaran', [TamuReservasiController::class, 'bayar'])->name('reservasi.bayar');
    Route::post('reservasi/{reservasi}/pembatalan', [TamuReservasiController::class, 'batal'])->name('reservasi.batal');
});

/*
| Halaman tamu: hanya untuk tamu yang sudah login.
*/
Route::prefix('tamu')->name('tamu.')->middleware(['auth'])->group(function (): void {
    Route::view('dashboard', 'tamu.dashboard')->name('dashboard');
});

/*
| Halaman admin: hanya untuk petugas (admin & resepsionis). Datanya diambil lewat API v1
| (lihat routes/api.php dan docs/API-ENDPOINTS.md).
*/
Route::prefix('admin')->name('admin.')
    ->middleware(['auth', 'peran:'.implode(',', array_map(fn (Peran $p) => $p->value, Peran::staf()))])
    ->group(function (): void {
        Route::view('dashboard', 'admin.dashboard')->name('dashboard');
        Route::view('reservasi', 'admin.reservasi')->name('reservasi');
        Route::view('tamu', 'admin.tamu')->name('tamu');
        Route::view('pembayaran', 'admin.pembayaran')->name('pembayaran');
        Route::view('pembatalan', 'admin.pembatalan')->name('pembatalan');
        Route::view('pengguna', 'admin.pengguna')->name('pengguna');
    });
