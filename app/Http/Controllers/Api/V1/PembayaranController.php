<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    /** Daftar semua pembayaran (admin) */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->validate([
            'status'   => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $data = Pembayaran::with(['reservasi.tamu'])
            ->when($filter['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($filter['per_page'] ?? 15);

        return response()->json($data);
    }

    /** Upload bukti pembayaran (tamu) */
    public function uploadBukti(Request $request, Reservasi $reservasi): JsonResponse
    {
        $request->validate([
            'bukti_pembayaran'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'metode_pembayaran' => ['required', 'string'],
        ]);

        $path = $request->file('bukti_pembayaran')->store('bukti-pembayaran', 'public');

        $pembayaran = Pembayaran::updateOrCreate(
            ['reservasi_id' => $reservasi->id],
            [
                'bukti_bayar'      => $path,
                'metode'           => $request->metode_pembayaran,
                'status'           => 'menunggu_verifikasi',
                'tgl_bayar'        => now(),
                'jumlah_bayar'     => $reservasi->total_harga,
            ]
        );

        $reservasi->update(['status_pembayaran' => 'menunggu_verifikasi']);

        return response()->json([
            'data'    => $pembayaran,
            'message' => 'Bukti pembayaran berhasil diunggah.',
        ]);
    }

    /** Verifikasi atau tolak pembayaran (admin) */
    public function verifikasi(Request $request, Pembayaran $pembayaran): JsonResponse
    {
        $data = $request->validate([
            'status'  => ['required', 'in:lunas,ditolak'],
            'catatan' => ['nullable', 'string'],
        ]);

        $pembayaran->update([
            'status'            => $data['status'],
            'catatan'           => $data['catatan'] ?? null,
            'diverifikasi_oleh' => $request->user()->id,
        ]);

        if ($data['status'] === 'lunas') {
            $pembayaran->reservasi->update(['status_pembayaran' => 'lunas']);
        }

        return response()->json([
            'data'    => $pembayaran,
            'message' => 'Status pembayaran diperbarui.',
        ]);
    }
}
