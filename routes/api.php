<?php

use App\Enums\Peran;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\KamarController;
use App\Http\Controllers\Api\V1\ReservasiController;
use App\Http\Controllers\Api\V1\ReservasiStatusController;
use App\Http\Controllers\Api\V1\TamuController;
use App\Http\Controllers\Api\V1\TipeKamarController;
use Illuminate\Support\Facades\Route;

/*
| API v1 — prefix otomatis /api, jadi alamatnya /api/v1/...
|
| Memakai grup middleware "web" (session + CSRF) karena halaman Blade memanggil API dari
| domain yang sama. Frontend wajib mengirim header X-CSRF-TOKEN (lihat resources/js/lib/api.js).
|
| Hak akses:
|   publik          : tipe kamar, cek kamar kosong, booking tamu, login, registrasi
|   login (semua)   : /auth/me, /auth/logout
|   admin+resepsionis: lihat kamar, kelola tamu & reservasi, ubah status
|   admin saja      : tambah/ubah/hapus kamar, hapus tamu, hapus reservasi
*/
$staf = 'peran:'.implode(',', array_map(fn (Peran $p) => $p->value, Peran::staf()));

Route::prefix('v1')->name('api.v1.')->middleware('web')->group(function () use ($staf): void {
    // --- Publik ---
    Route::get('tipe-kamar', [TipeKamarController::class, 'index'])->name('tipe-kamar.index');
    Route::get('kamar/tersedia', [KamarController::class, 'tersedia'])->name('kamar.tersedia');

    Route::post('booking', [BookingController::class, 'store'])->middleware('throttle:20,1')->name('booking.store');
    Route::get('booking/{kode}', [BookingController::class, 'show'])->middleware('throttle:20,1')->name('booking.show');

    Route::post('auth/registrasi', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('auth.registrasi');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

    // --- Semua akun yang sudah login ---
    Route::middleware('auth')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
    });

    // --- Petugas (admin & resepsionis) ---
    Route::middleware(['auth', $staf])->group(function (): void {
        Route::apiResource('kamar', KamarController::class)->only(['index', 'show'])->parameters(['kamar' => 'kamar']);
        Route::apiResource('tamu', TamuController::class)->except('destroy')->parameters(['tamu' => 'tamu']);
        Route::apiResource('reservasi', ReservasiController::class)->except('destroy')->parameters(['reservasi' => 'reservasi']);
        Route::post('reservasi/{reservasi}/status', ReservasiStatusController::class)->name('reservasi.status');
    });

    // --- Admin saja ---
    Route::middleware(['auth', 'peran:admin'])->group(function (): void {
        Route::apiResource('kamar', KamarController::class)->only(['store', 'update', 'destroy'])->parameters(['kamar' => 'kamar']);
        Route::delete('tamu/{tamu}', [TamuController::class, 'destroy'])->name('tamu.destroy');
        Route::delete('reservasi/{reservasi}', [ReservasiController::class, 'destroy'])->name('reservasi.destroy');
    });
});
