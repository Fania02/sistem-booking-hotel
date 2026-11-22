<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Pembatalan;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/** Angka-angka untuk dashboard admin. */
class AdminDashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $hari = today();

        $totalKamar = Kamar::query()->count();
        $terisi = DB::table('detail_reservasi')
            ->join('reservasi', 'reservasi.id', '=', 'detail_reservasi.reservasi_id')
            ->whereIn('reservasi.status', [StatusReservasi::Dikonfirmasi->value, StatusReservasi::Checkin->value])
            ->whereDate('reservasi.tgl_checkin', '<=', $hari)
            ->whereDate('reservasi.tgl_checkout', '>', $hari)
            ->distinct()
            ->count('detail_reservasi.kamar_id');

        // Kedatangan 7 hari ke depan (dihitung di PHP agar tidak bergantung jenis database)
        $kedatangan = Reservasi::query()
            ->where('status', '!=', StatusReservasi::Batal->value)
            ->whereDate('tgl_checkin', '>=', $hari)
            ->whereDate('tgl_checkin', '<=', $hari->copy()->addDays(6))
            ->get(['id', 'tgl_checkin'])
            ->countBy(fn (Reservasi $r) => $r->tgl_checkin->toDateString());

        $grafik = collect(range(0, 6))->map(function (int $i) use ($hari, $kedatangan): array {
            $tgl = $hari->copy()->addDays($i);

            return ['tanggal' => $tgl->toDateString(), 'hari' => $tgl->locale('id')->isoFormat('ddd'), 'jumlah' => $kedatangan->get($tgl->toDateString(), 0)];
        });

        $terbaru = Reservasi::query()->with('tamu')->latest('id')->limit(6)->get()->map(fn (Reservasi $r) => [
            'id' => $r->id,
            'kode_booking' => $r->kode_booking,
            'tamu' => $r->tamu?->nama,
            'tgl_checkin' => $r->tgl_checkin->toDateString(),
            'tgl_checkout' => $r->tgl_checkout->toDateString(),
            'total_harga' => (float) $r->total_harga,
            'status' => $r->status->opsi(),
        ]);

        return response()->json(['data' => [
            'checkin_hari_ini' => Reservasi::query()->whereIn('status', ['menunggu', 'dikonfirmasi'])->whereDate('tgl_checkin', $hari)->count(),
            'checkout_hari_ini' => Reservasi::query()->where('status', 'checkin')->whereDate('tgl_checkout', $hari)->count(),
            'total_kamar' => $totalKamar,
            'kamar_terisi' => $terisi,
            'okupansi' => $totalKamar > 0 ? round($terisi / $totalKamar * 100) : 0,
            'pendapatan_bulan_ini' => (float) Pembayaran::query()
                ->where('status', 'diterima')
                ->whereBetween('tgl_bayar', [$hari->copy()->startOfMonth(), $hari->copy()->endOfMonth()])
                ->sum('jumlah_bayar'),
            'pembayaran_menunggu' => Pembayaran::query()->where('status', 'menunggu_verifikasi')->count(),
            'pembatalan_diajukan' => Pembatalan::query()->where('status', 'diajukan')->count(),
            'grafik_kedatangan' => $grafik,
            'reservasi_terbaru' => $terbaru,
        ]]);
    }
}
