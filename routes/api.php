<?php

use App\Enums\Peran;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FasilitasController;
use App\Http\Controllers\Api\V1\KamarController;
use App\Http\Controllers\Api\V1\PembatalanController;
use App\Http\Controllers\Api\V1\PembayaranController;
use App\Http\Controllers\Api\V1\PenggunaController;
use App\Http\Controllers\Api\V1\ReservasiController;
use App\Http\Controllers\Api\V1\ReservasiStatusController;
use App\Http\Controllers\Api\V1\SayaController;
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
|   tamu             : /saya/* (riwayat, bayar, ajukan pembatalan)
|   admin saja      : kamar/tipe kamar/fasilitas (tambah-ubah-hapus), akun petugas, hapus tamu & reservasi
*/
$staf = 'peran:'.implode(',', array_map(fn (Peran $p) => $p->value, Peran::staf()));

Route::prefix('v1')->name('api.v1.')->middleware('web')->group(function () use ($staf): void {
    // --- Publik ---
    Route::get('tipe-kamar', [TipeKamarController::class, 'index'])->name('tipe-kamar.index');
    Route::get('tipe-kamar/{tipe_kamar}', [TipeKamarController::class, 'show'])->name('tipe-kamar.show');
    Route::get('fasilitas', [FasilitasController::class, 'index'])->name('fasilitas.index');
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

    // --- Tamu ---
    Route::middleware(['auth', 'peran:tamu'])->prefix('saya')->name('saya.')->group(function (): void {
        Route::get('reservasi', [SayaController::class, 'reservasi'])->name('reservasi');
        Route::post('reservasi/{reservasi}/pembayaran', [SayaController::class, 'bayar'])->middleware('throttle:20,1')->name('bayar');
        Route::post('reservasi/{reservasi}/pembatalan', [SayaController::class, 'ajukanPembatalan'])->name('pembatalan');
    });

    // --- Petugas (admin & resepsionis) ---
    Route::middleware(['auth', $staf])->group(function (): void {
        Route::apiResource('kamar', KamarController::class)->only(['index', 'show'])->parameters(['kamar' => 'kamar']);
        Route::apiResource('tamu', TamuController::class)->except('destroy')->parameters(['tamu' => 'tamu']);
        Route::apiResource('reservasi', ReservasiController::class)->except('destroy')->parameters(['reservasi' => 'reservasi']);
        Route::post('reservasi/{reservasi}/status', ReservasiStatusController::class)->name('reservasi.status');

        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
        Route::post('pembayaran/{pembayaran}/verifikasi', [PembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
        Route::get('reservasi/{reservasi}/pembayaran', [PembayaranController::class, 'perReservasi'])->name('reservasi.pembayaran.index');
        Route::post('reservasi/{reservasi}/pembayaran', [PembayaranController::class, 'store'])->name('reservasi.pembayaran.store');

        Route::get('pembatalan', [PembatalanController::class, 'index'])->name('pembatalan.index');
        Route::post('pembatalan/{pembatalan}/proses', [PembatalanController::class, 'proses'])->name('pembatalan.proses');
    });

    // --- Admin saja ---
    Route::middleware(['auth', 'peran:admin'])->group(function (): void {
        Route::apiResource('kamar', KamarController::class)->only(['store', 'update', 'destroy'])->parameters(['kamar' => 'kamar']);
        Route::delete('tamu/{tamu}', [TamuController::class, 'destroy'])->name('tamu.destroy');
        Route::delete('reservasi/{reservasi}', [ReservasiController::class, 'destroy'])->name('reservasi.destroy');

        Route::apiResource('tipe-kamar', TipeKamarController::class)->only(['store', 'update', 'destroy'])->parameters(['tipe-kamar' => 'tipe_kamar']);
        Route::apiResource('fasilitas', FasilitasController::class)->only(['store', 'update', 'destroy'])->parameters(['fasilitas' => 'fasilitas']);
        Route::apiResource('pengguna', PenggunaController::class)->parameters(['pengguna' => 'pengguna']);
    });
});
