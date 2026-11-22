<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pembatalan;
use App\Models\Reservasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PembatalanController extends Controller
{
    /** Daftar semua pembatalan (admin) */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->validate([
            'status'   => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $data = Pembatalan::with(['reservasi.tamu'])
            ->when($filter['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate($filter['per_page'] ?? 15);

        return response()->json($data);
    }

    /** Ajukan pembatalan dari tamu */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reservasi_id'      => ['required', 'integer', 'exists:reservasi,id'],
            'alasan_pembatalan' => ['required', 'string', 'min:10'],
        ]);

        $reservasi = Reservasi::findOrFail($data['reservasi_id']);

        if (! in_array($reservasi->status->value, ['menunggu', 'dikonfirmasi'], true)) {
            return response()->json(['message' => 'Reservasi tidak dapat dibatalkan pada status ini.'], 422);
        }

        // Cek belum ada pengajuan menunggu
        if ($reservasi->pembatalan()->where('status', 'menunggu')->exists()) {
            return response()->json(['message' => 'Sudah ada pengajuan pembatalan yang sedang diproses.'], 409);
        }

        $pembatalan = Pembatalan::create([
            'reservasi_id'  => $reservasi->id,
            'alasan'        => $data['alasan_pembatalan'],
            'tgl_pengajuan' => now(),
            'status'        => 'menunggu',
        ]);

        return response()->json([
            'data'    => $pembatalan,
            'message' => 'Permintaan pembatalan berhasil dikirim.',
        ], 201);
    }

    /** Proses pembatalan — setujui atau tolak (admin) */
    public function update(Request $request, Pembatalan $pembatalan): JsonResponse
    {
        $data = $request->validate([
            'status'        => ['required', 'in:disetujui,ditolak'],
            'catatan_admin' => ['nullable', 'string'],
        ]);

        $pembatalan->update([
            'status'          => $data['status'],
            'catatan_petugas' => $data['catatan_admin'] ?? null,
            'diproses_oleh'   => $request->user()->id,
            'tgl_diproses'    => now(),
        ]);

        // Jika disetujui, ubah status reservasi ke batal
        if ($data['status'] === 'disetujui') {
            $pembatalan->reservasi()->update(['status' => 'batal']);
        }

        return response()->json([
            'data'    => $pembatalan,
            'message' => 'Pembatalan ' . ($data['status'] === 'disetujui' ? 'disetujui.' : 'ditolak.'),
        ]);
    }
}
