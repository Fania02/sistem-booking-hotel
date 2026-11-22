<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Pembatalan;
use App\Services\ReservasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Persetujuan pengajuan pembatalan dari tamu. */
class AdminPembatalanController extends Controller
{
    public function __construct(private ReservasiService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filter = $request->validate(['status' => ['nullable', 'in:diajukan,disetujui,ditolak']]);

        $daftar = Pembatalan::query()
            ->with(['reservasi.tamu', 'pemroses'])
            ->when($filter['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->limit(100)
            ->get();

        return response()->json(['data' => $daftar->map(fn (Pembatalan $p) => $this->bentuk($p))->values()]);
    }

    public function proses(Request $request, Pembatalan $pembatalan): JsonResponse
    {
        $data = $request->validate([
            'keputusan' => ['required', 'in:setujui,tolak'],
            'catatan' => ['nullable', 'string', 'max:500', 'required_if:keputusan,tolak'],
        ], ['catatan.required_if' => 'Tulis alasan penolakan untuk tamu.']);

        if ($data['keputusan'] === 'setujui') {
            $this->service->setujuiPembatalan($pembatalan, $request->user()->id, $data['catatan'] ?? null);
        } else {
            if ($pembatalan->status !== 'diajukan') {
                throw ValidationException::withMessages(['keputusan' => 'Pengajuan ini sudah diproses.']);
            }

            $pembatalan->forceFill([
                'status' => 'ditolak',
                'diproses_oleh' => $request->user()->id,
                'tgl_diproses' => now(),
                'catatan_petugas' => $data['catatan'],
            ])->save();
        }

        return response()->json(['data' => $this->bentuk($pembatalan->fresh(['reservasi.tamu', 'pemroses']))]);
    }

    /** @return array<string, mixed> */
    private function bentuk(Pembatalan $p): array
    {
        return [
            'id' => $p->id,
            'status' => $p->status,
            'alasan' => $p->alasan,
            'catatan_petugas' => $p->catatan_petugas,
            'tgl_pengajuan' => $p->tgl_pengajuan?->toIso8601String(),
            'tgl_diproses' => $p->tgl_diproses?->toIso8601String(),
            'diproses_oleh' => $p->pemroses?->name,
            'reservasi' => [
                'id' => $p->reservasi->id,
                'kode_booking' => $p->reservasi->kode_booking,
                'tgl_checkin' => $p->reservasi->tgl_checkin->toDateString(),
                'tgl_checkout' => $p->reservasi->tgl_checkout->toDateString(),
                'total_harga' => (float) $p->reservasi->total_harga,
                'status' => $p->reservasi->status->opsi(),
                'status_pembayaran' => $p->reservasi->status_pembayaran->opsi(),
            ],
            'tamu' => ['nama' => $p->reservasi->tamu?->nama, 'no_hp' => $p->reservasi->tamu?->no_hp],
        ];
    }
}
