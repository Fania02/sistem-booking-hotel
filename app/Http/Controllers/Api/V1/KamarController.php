<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusKamar;
use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\CekKetersediaanRequest;
use App\Http\Requests\KamarRequest;
use App\Http\Resources\KamarResource;
use App\Models\Kamar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class KamarController extends Controller
{
    /**
     * Daftar kamar. Filter: search (nomor kamar), tipe_kamar_id, status, lantai. Pagination: per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filter = $request->validate([
            'search' => ['nullable', 'string', 'max:50'],
            'tipe_kamar_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(StatusKamar::class)],
            'lantai' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $kamar = Kamar::query()
            ->with('tipeKamar')
            ->when($filter['search'] ?? null, fn ($q, $cari) => $q->where('nomor_kamar', 'like', "%{$cari}%"))
            ->when($filter['tipe_kamar_id'] ?? null, fn ($q, $id) => $q->where('tipe_kamar_id', $id))
            ->when($filter['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filter['lantai'] ?? null, fn ($q, $lantai) => $q->where('lantai', $lantai))
            ->orderBy('nomor_kamar')
            ->paginate($filter['per_page'] ?? 15);

        return KamarResource::collection($kamar);
    }

    /**
     * Kamar yang kosong pada rentang tanggal tertentu (untuk form reservasi).
     */
    public function tersedia(CekKetersediaanRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();

        $kamar = Kamar::query()
            ->with('tipeKamar')
            ->tersedia($data['tgl_checkin'], $data['tgl_checkout'], $data['abaikan_reservasi_id'] ?? null)
            ->when($data['tipe_kamar_id'] ?? null, fn ($q, $id) => $q->where('tipe_kamar_id', $id))
            ->orderBy('nomor_kamar')
            ->get();

        return KamarResource::collection($kamar);
    }

    public function store(KamarRequest $request): JsonResponse
    {
        $kamar = Kamar::create($request->validated())->load('tipeKamar');

        return (new KamarResource($kamar))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Kamar $kamar): KamarResource
    {
        return new KamarResource($kamar->load('tipeKamar'));
    }

    public function update(KamarRequest $request, Kamar $kamar): KamarResource
    {
        $kamar->update($request->validated());

        return new KamarResource($kamar->load('tipeKamar'));
    }

    public function destroy(Kamar $kamar): Response|JsonResponse
    {
        $dipakai = $kamar->detailReservasi()
            ->whereHas('reservasi', fn ($q) => $q->whereIn('status', StatusReservasi::nilaiAktif()))
            ->exists();

        if ($dipakai) {
            return response()->json([
                'message' => 'Kamar masih dipakai reservasi aktif sehingga tidak dapat dihapus.',
            ], Response::HTTP_CONFLICT);
        }

        $kamar->delete();

        return response()->noContent();
    }
}
