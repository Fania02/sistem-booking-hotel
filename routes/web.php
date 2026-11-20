<?php

use App\Enums\Peran;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('beranda');

Route::middleware('guest')->group(function (): void {
    Route::view('masuk', 'auth.masuk')->name('login');
    Route::view('daftar', 'auth.daftar')->name('daftar');
});

/*
| Halaman admin: hanya untuk petugas (admin & resepsionis). Datanya diambil lewat API v1
| (lihat routes/api.php dan docs/API-ENDPOINTS.md).
*/
Route::prefix('admin')->name('admin.')
    ->middleware(['auth', 'peran:'.implode(',', array_map(fn (Peran $p) => $p->value, Peran::staf()))])
    ->group(function (): void {
        Route::view('reservasi', 'admin.reservasi')->name('reservasi');
        Route::view('tamu', 'admin.tamu')->name('tamu');
    });
