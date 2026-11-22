<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Peran;
use App\Enums\StatusKamar;
use App\Enums\StatusPembatalan;
use App\Enums\StatusReservasi;
use App\Enums\StatusVerifikasi;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReservasiResource;
use App\Models\DetailReservasi;
use App\Models\Kamar;
use App\Models\Pembatalan;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Ringkasan untuk halaman dashboard petugas. Pendapatan hanya dikirim ke admin.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $hariIni = now()->toDateString();

        $perStatus = Reservasi::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $terisi = DetailReservasi::query()
            ->whereHas('reservasi', fn ($q) => $q->where('status', StatusReservasi::Checkin->value))
            ->distinct()
            ->count('kamar_id');

        $data = [
            'reservasi' => [
                'per_status' => collect(StatusReservasi::cases())
                    ->mapWithKeys(fn (StatusReservasi $s) => [$s->value => (int) ($perStatus[$s->value] ?? 0)])
                    ->all(),
                'checkin_hari_ini' => Reservasi::query()->where('status', StatusReservasi::Dikonfirmasi->value)->whereDate('tgl_checkin', $hariIni)->count(),
                'checkout_hari_ini' => Reservasi::query()->where('status', StatusReservasi::Checkin->value)->whereDate('tgl_checkout', $hariIni)->count(),
            ],
            'kamar' => [
                'total' => Kamar::query()->count(),
                'perbaikan' => Kamar::query()->where('status', StatusKamar::Perbaikan->value)->count(),
                'terisi' => $terisi,
            ],
            'perlu_tindakan' => [
                'pembayaran_menunggu_verifikasi' => Pembayaran::query()->where('status', StatusVerifikasi::MenungguVerifikasi->value)->count(),
                'pembatalan_diajukan' => Pembatalan::query()->where('status', StatusPembatalan::Diajukan->value)->count(),
            ],
            'reservasi_terbaru' => ReservasiResource::collection(
                Reservasi::query()->with(['tamu', 'detail.kamar.tipeKamar'])->latest('id')->limit(5)->get(),
            ),
        ];

        if ($request->user()->punyaPeran(Peran::Admin)) {
            $diterima = Pembayaran::query()->where('status', StatusVerifikasi::Diterima->value);

            $data['pendapatan'] = [
                'hari_ini' => (float) (clone $diterima)->whereDate('tgl_bayar', $hariIni)->sum('jumlah_bayar'),
                'bulan_ini' => (float) (clone $diterima)->whereYear('tgl_bayar', now()->year)->whereMonth('tgl_bayar', now()->month)->sum('jumlah_bayar'),
            ];
        }

        return response()->json(['data' => $data]);
    }
}
