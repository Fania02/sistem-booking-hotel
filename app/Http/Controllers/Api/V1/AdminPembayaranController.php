<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusPembayaran;
use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Verifikasi bukti pembayaran oleh petugas. */
class AdminPembayaranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filter = $request->validate(['status' => ['nullable', 'in:menunggu_verifikasi,diterima,ditolak']]);

        $daftar = Pembayaran::query()
            ->with(['reservasi.tamu', 'verifikator'])
            ->when($filter['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->limit(100)
            ->get();

        return response()->json(['data' => $daftar->map(fn (Pembayaran $p) => $this->bentuk($p))->values()]);
    }

    public function verifikasi(Request $request, Pembayaran $pembayaran): JsonResponse
    {
        $data = $request->validate([
            'keputusan' => ['required', 'in:terima,tolak'],
            'catatan' => ['nullable', 'string', 'max:500', 'required_if:keputusan,tolak'],
        ], ['catatan.required_if' => 'Tulis alasan penolakan agar tamu tahu apa yang harus diperbaiki.']);

        DB::transaction(function () use ($pembayaran, $data, $request): void {
            $pembayaran = Pembayaran::query()->lockForUpdate()->findOrFail($pembayaran->id);

            if ($pembayaran->status !== 'menunggu_verifikasi') {
                throw ValidationException::withMessages(['keputusan' => 'Pembayaran ini sudah diverifikasi.']);
            }

            $pembayaran->status = $data['keputusan'] === 'terima' ? 'diterima' : 'ditolak';
            $pembayaran->diverifikasi_oleh = $request->user()->id;
            $pembayaran->catatan = $data['catatan'] ?? null;
            $pembayaran->save();

            // Hitung ulang status pembayaran reservasi dari total yang sudah DITERIMA
            $reservasi = $pembayaran->reservasi;
            $diterima = (float) $reservasi->pembayaran()->where('status', 'diterima')->sum('jumlah_bayar');

            $reservasi->status_pembayaran = match (true) {
                $diterima >= (float) $reservasi->total_harga - 0.5 => StatusPembayaran::Lunas,
                $diterima > 0 => StatusPembayaran::Dp,
                default => StatusPembayaran::BelumBayar,
            };
            $reservasi->save();
        });

        return response()->json(['data' => $this->bentuk($pembayaran->fresh(['reservasi.tamu', 'verifikator']))]);
    }

    /** @return array<string, mixed> */
    private function bentuk(Pembayaran $p): array
    {
        return [
            'id' => $p->id,
            'jumlah_bayar' => (float) $p->jumlah_bayar,
            'metode' => $p->metode,
            'status' => $p->status,
            'catatan' => $p->catatan,
            'tgl_bayar' => $p->tgl_bayar?->toIso8601String(),
            'bukti_url' => $p->bukti_bayar ? '/storage/'.$p->bukti_bayar : null,
            'diverifikasi_oleh' => $p->verifikator?->name,
            'reservasi' => [
                'id' => $p->reservasi->id,
                'kode_booking' => $p->reservasi->kode_booking,
                'total_harga' => (float) $p->reservasi->total_harga,
                'status_pembayaran' => $p->reservasi->status_pembayaran->opsi(),
            ],
            'tamu' => ['nama' => $p->reservasi->tamu?->nama, 'no_hp' => $p->reservasi->tamu?->no_hp],
        ];
    }
}
