<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FasilitasController;
use App\Http\Controllers\Api\V1\KamarController;
use App\Http\Controllers\Api\V1\PembatalanController;
use App\Http\Controllers\Api\V1\PembayaranController;
use App\Http\Controllers\Api\V1\PenggunaController;
use App\Http\Controllers\Api\V1\ReservasiController;
use App\Http\Controllers\Api\V1\ReservasiStatusController;
use App\Http\Controllers\Api\V1\TamuController;
use App\Http\Controllers\Api\V1\TipeKamarController;
use Illuminate\Support\Facades\Route;

/*
| API v1 — prefix otomatis /api, jadi alamatnya /api/v1/...
*/
Route::prefix('v1')->name('api.v1.')->group(function (): void {

    /* ---- Auth ---- */
    Route::post('auth/login',      [AuthController::class, 'login'])->name('auth.login');
    Route::post('auth/registrasi', [AuthController::class, 'registrasi'])->name('auth.registrasi');
    Route::post('auth/logout',     [AuthController::class, 'logout'])->name('auth.logout');

    /* ---- Tipe Kamar ---- */
    Route::get('tipe-kamar',                    [TipeKamarController::class, 'index'])->name('tipe-kamar.index');
    Route::post('tipe-kamar',                   [TipeKamarController::class, 'store'])->name('tipe-kamar.store');
    Route::patch('tipe-kamar/{tipeKamar}',      [TipeKamarController::class, 'update'])->name('tipe-kamar.update');
    Route::delete('tipe-kamar/{tipeKamar}',     [TipeKamarController::class, 'destroy'])->name('tipe-kamar.destroy');

    /* ---- Fasilitas ---- */
    Route::get('fasilitas',         [FasilitasController::class, 'index'])->name('fasilitas.index');
    Route::post('fasilitas',        [FasilitasController::class, 'store'])->name('fasilitas.store');
    Route::delete('fasilitas/{id}', [FasilitasController::class, 'destroy'])->name('fasilitas.destroy');

    /* ---- Kamar ---- */
    // Harus dideklarasikan sebelum kamar/{kamar}
    Route::get('kamar/tersedia', [KamarController::class, 'tersedia'])->name('kamar.tersedia');
    Route::apiResource('kamar', KamarController::class)->parameters(['kamar' => 'kamar']);

    /* ---- Tamu ---- */
    Route::apiResource('tamu', TamuController::class)->parameters(['tamu' => 'tamu']);

    /* ---- Reservasi ---- */
    // Riwayat milik tamu sendiri — harus sebelum apiResource agar tidak bentrok
    Route::get('reservasi/saya', [ReservasiController::class, 'milikSaya'])->name('reservasi.saya');

    // Alias /booking → store reservasi (dipakai frontend tamu)
    Route::post('booking', [ReservasiController::class, 'store'])->name('booking');

    Route::apiResource('reservasi', ReservasiController::class)->parameters(['reservasi' => 'reservasi']);
    Route::post('reservasi/{reservasi}/status', ReservasiStatusController::class)->name('reservasi.status');

    /* ---- Pembayaran ---- */
    Route::get('pembayaran',                             [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('pembayaran/{reservasi}/bukti',          [PembayaranController::class, 'uploadBukti'])->name('pembayaran.bukti');
    Route::patch('pembayaran/{pembayaran}/verifikasi',   [PembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');

    /* ---- Pembatalan ---- */
    Route::get('pembatalan',               [PembatalanController::class, 'index'])->name('pembatalan.index');
    Route::post('pembatalan',              [PembatalanController::class, 'store'])->name('pembatalan.store');
    Route::patch('pembatalan/{pembatalan}',[PembatalanController::class, 'update'])->name('pembatalan.update');

    /* ---- Pengguna (admin) ---- */
    Route::get('pengguna',           [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::post('pengguna',          [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::patch('pengguna/{user}',  [PenggunaController::class, 'update'])->name('pengguna.update');
    Route::delete('pengguna/{user}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
});
