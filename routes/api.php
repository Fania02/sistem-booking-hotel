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
|--------------------------------------------------------------------------
| API v1
|--------------------------------------------------------------------------
| Menggunakan middleware web agar session dan CSRF berjalan untuk
| frontend Blade pada domain yang sama.
|
| Hak akses:
| - Publik: tipe kamar, fasilitas, kamar tersedia, booking, autentikasi.
| - Tamu: riwayat reservasi, pembayaran, pembatalan.
| - Admin dan resepsionis: pengelolaan operasional hotel.
| - Admin: pengelolaan data master dan akun petugas.
*/

$staf = 'peran:'.implode(',', array_map(
    fn (Peran $p) => $p->value,
    Peran::staf()
));

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('web')
    ->group(function () use ($staf): void {

        // =========================
        // PUBLIK
        // =========================

        Route::get('tipe-kamar', [TipeKamarController::class, 'index'])
            ->name('tipe-kamar.index');

        Route::get('tipe-kamar/{tipe_kamar}', [TipeKamarController::class, 'show'])
            ->name('tipe-kamar.show');

        Route::get('fasilitas', [FasilitasController::class, 'index'])
            ->name('fasilitas.index');

        Route::get('kamar/tersedia', [KamarController::class, 'tersedia'])
            ->name('kamar.tersedia');

        // Booking publik
        Route::post('booking', [BookingController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('booking.store');

        Route::get('booking/{kode}', [BookingController::class, 'show'])
            ->middleware('throttle:20,1')
            ->name('booking.show');

        // =========================
        // AUTENTIKASI
        // =========================

        Route::post('auth/registrasi', [AuthController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('auth.registrasi');

        Route::post('auth/login', [AuthController::class, 'login'])
            ->name('auth.login');

        Route::middleware('auth')->group(function (): void {
            Route::post('auth/logout', [AuthController::class, 'logout'])
                ->name('auth.logout');

            Route::get('auth/me', [AuthController::class, 'me'])
                ->name('auth.me');
        });

        // =========================
        // TAMU
        // =========================

        Route::middleware(['auth', 'peran:tamu'])
            ->prefix('saya')
            ->name('saya.')
            ->group(function (): void {

                Route::get('reservasi', [SayaController::class, 'reservasi'])
                    ->name('reservasi');

                Route::post('reservasi/{reservasi}/pembayaran', [SayaController::class, 'bayar'])
                    ->middleware('throttle:20,1')
                    ->name('bayar');

                Route::post('reservasi/{reservasi}/pembatalan', [SayaController::class, 'ajukanPembatalan'])
                    ->name('pembatalan');
            });

        // =========================
        // ADMIN DAN RESEPSIONIS
        // =========================

        Route::middleware(['auth', $staf])->group(function (): void {

            // Dashboard
            Route::get('dashboard', DashboardController::class)
                ->name('dashboard');

            // Kamar
            Route::apiResource('kamar', KamarController::class)
                ->only(['index', 'show'])
                ->parameters(['kamar' => 'kamar']);

            // Tamu
            Route::apiResource('tamu', TamuController::class)
                ->except(['destroy'])
                ->parameters(['tamu' => 'tamu']);

            // Reservasi
            Route::apiResource('reservasi', ReservasiController::class)
                ->except(['destroy'])
                ->parameters(['reservasi' => 'reservasi']);

            Route::post('reservasi/{reservasi}/status', ReservasiStatusController::class)
                ->name('reservasi.status');

            // Pembayaran
            Route::get('pembayaran', [PembayaranController::class, 'index'])
                ->name('pembayaran.index');

            Route::post('pembayaran/{pembayaran}/verifikasi', [PembayaranController::class, 'verifikasi'])
                ->name('pembayaran.verifikasi');

            Route::get('reservasi/{reservasi}/pembayaran', [PembayaranController::class, 'perReservasi'])
                ->name('reservasi.pembayaran.index');

            Route::post('reservasi/{reservasi}/pembayaran', [PembayaranController::class, 'store'])
                ->name('reservasi.pembayaran.store');

            // Pembatalan
            Route::get('pembatalan', [PembatalanController::class, 'index'])
                ->name('pembatalan.index');

            Route::post('pembatalan/{pembatalan}/proses', [PembatalanController::class, 'proses'])
                ->name('pembatalan.proses');
        });

        // =========================
        // ADMIN SAJA
        // =========================

        Route::middleware(['auth', 'peran:admin'])->group(function (): void {

            // Kamar
            Route::apiResource('kamar', KamarController::class)
                ->only(['store', 'update', 'destroy'])
                ->parameters(['kamar' => 'kamar']);

            // Tamu dan reservasi
            Route::delete('tamu/{tamu}', [TamuController::class, 'destroy'])
                ->name('tamu.destroy');

            Route::delete('reservasi/{reservasi}', [ReservasiController::class, 'destroy'])
                ->name('reservasi.destroy');

            // Tipe kamar
            Route::apiResource('tipe-kamar', TipeKamarController::class)
                ->only(['store', 'update', 'destroy'])
                ->parameters(['tipe-kamar' => 'tipe_kamar']);

            // Fasilitas
            Route::apiResource('fasilitas', FasilitasController::class)
                ->only(['store', 'update', 'destroy'])
                ->parameters(['fasilitas' => 'fasilitas');

            // Pengguna
            Route::apiResource('pengguna', PenggunaController::class)
                ->parameters(['pengguna' => 'pengguna']);
        });
    });