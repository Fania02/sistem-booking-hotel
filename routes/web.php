<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
Route::view('/', 'landing')->name('beranda');

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
        Route::view('dashboard',  'admin.dashboard')->name('dashboard');
        Route::view('reservasi',  'admin.reservasi')->name('reservasi');
        Route::view('tamu',       'admin.tamu')->name('tamu');
        Route::view('pembayaran', 'admin.pembayaran')->name('pembayaran');
        Route::view('pembatalan', 'admin.pembatalan')->name('pembatalan');
        Route::view('pengguna',   'admin.pengguna')->name('pengguna');
});