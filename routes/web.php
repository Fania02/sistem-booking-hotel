<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::view('/', 'landing')->name('beranda');

/*
| Halaman admin. Datanya diambil lewat API v1 (lihat routes/api.php dan docs/API-ENDPOINTS.md).
|
| TODO: bungkus grup ini dengan middleware auth setelah modul login dari anggota tim lain selesai.
*/
Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::view('reservasi', 'admin.reservasi')->name('reservasi');
    Route::view('tamu', 'admin.tamu')->name('tamu');
});
    
