<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Http\Resources\RiwayatReservasiResource;
use App\Models\Pembayaran;
use App\Models\Pembatalan;
use App\Models\Reservasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

/** Riwayat, upload bukti bayar, dan pengajuan pembatalan milik tamu yang sedang login. */
class TamuReservasiController extends Controller
{
    private const RELASI = ['detail.kamar.tipeKamar', 'pembayaran', 'pembatalan'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $tamu = $request->user()->tamu;

        $daftar = $tamu === null
            ? collect()
            : Reservasi::query()->where('tamu_id', $tamu->id)->with(self::RELASI)->latest('id')->get();

        return RiwayatReservasiResource::collection($daftar);
    }

    public function bayar(Request $request, Reservasi $reservasi): JsonResponse
    {
        $this->pastikanMilikSaya($request, $reservasi);

        $data = $request->validate([
            'jumlah_bayar' => ['required', 'numeric', 'min:10000'],
            'metode' => ['required', 'in:transfer,qris'],
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [
            'bukti.required' => 'Pilih file bukti pembayaran.',
            'bukti.mimes' => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'bukti.max' => 'Ukuran bukti maksimal 4 MB.',
        ]);

        $reservasi->load('pembayaran');
        $this->pastikanStatus($reservasi, 'Pembayaran hanya bisa dikirim untuk reservasi yang masih aktif.');

        $terpakai = (float) $reservasi->pembayaran->whereIn('status', ['diterima', 'menunggu_verifikasi'])->sum('jumlah_bayar');
        $sisa = max(0, (float) $reservasi->total_harga - $terpakai);

        if ((float) $data['jumlah_bayar'] > $sisa + 0.5) {
            throw ValidationException::withMessages([
                'jumlah_bayar' => 'Jumlah melebihi sisa tagihan (Rp '.number_format($sisa, 0, ',', '.').').',
            ]);
        }

        Pembayaran::create([
            'reservasi_id' => $reservasi->id,
            'jumlah_bayar' => $data['jumlah_bayar'],
            'metode' => $data['metode'],
            'bukti_bayar' => $request->file('bukti')->store('bukti-bayar', 'public'),
            'tgl_bayar' => now(),
            'status' => 'menunggu_verifikasi',
        ]);

        return $this->balas($reservasi, 201);
    }

    public function batal(Request $request, Reservasi $reservasi): JsonResponse
    {
        $this->pastikanMilikSaya($request, $reservasi);

        $data = $request->validate([
            'alasan' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->pastikanStatus($reservasi, 'Hanya reservasi yang belum check-in yang bisa dibatalkan.');

        if ($reservasi->pembatalan()->where('status', 'diajukan')->exists()) {
            throw ValidationException::withMessages(['alasan' => 'Pengajuan pembatalan sebelumnya masih menunggu persetujuan.']);
        }

        Pembatalan::create([
            'reservasi_id' => $reservasi->id,
            'alasan' => $data['alasan'],
            'tgl_pengajuan' => now(),
            'status' => 'diajukan',
        ]);

        return $this->balas($reservasi, 201);
    }

    /** Reservasi milik tamu lain dianggap tidak ada (404), bukan "dilarang". */
    private function pastikanMilikSaya(Request $request, Reservasi $reservasi): void
    {
        abort_unless($request->user()->tamu?->id === $reservasi->tamu_id, 404);
    }

    private function pastikanStatus(Reservasi $reservasi, string $pesan): void
    {
        if (! in_array($reservasi->status, [StatusReservasi::Menunggu, StatusReservasi::Dikonfirmasi], true)) {
            throw ValidationException::withMessages(['status' => $pesan]);
        }
    }

    private function balas(Reservasi $reservasi, int $kode): JsonResponse
    {
        return (new RiwayatReservasiResource($reservasi->fresh(self::RELASI)))->response()->setStatusCode($kode);
    }
}
