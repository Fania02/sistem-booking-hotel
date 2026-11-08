<?php

use App\Http\Controllers\Api\V1\KamarController;
use App\Http\Controllers\Api\V1\ReservasiController;
use App\Http\Controllers\Api\V1\ReservasiStatusController;
use App\Http\Controllers\Api\V1\TamuController;
use App\Http\Controllers\Api\V1\TipeKamarController;
use Illuminate\Support\Facades\Route;

/*
| API v1 — prefix otomatis /api, jadi alamatnya /api/v1/...
|
| TODO: bungkus grup ini dengan middleware auth (mis. 'auth:sanctum') setelah login
| dari anggota tim lain selesai. Sementara dibiarkan terbuka agar frontend bisa dicoba.
*/
Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('tipe-kamar', [TipeKamarController::class, 'index'])->name('tipe-kamar.index');

    // Harus dideklarasikan sebelum kamar/{kamar}
    Route::get('kamar/tersedia', [KamarController::class, 'tersedia'])->name('kamar.tersedia');
    Route::apiResource('kamar', KamarController::class)->parameters(['kamar' => 'kamar']);

    Route::apiResource('tamu', TamuController::class)->parameters(['tamu' => 'tamu']);

    Route::apiResource('reservasi', ReservasiController::class)->parameters(['reservasi' => 'reservasi']);
    Route::post('reservasi/{reservasi}/status', ReservasiStatusController::class)->name('reservasi.status');
});
