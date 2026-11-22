<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusPembatalan;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProsesPembatalanRequest;
use App\Http\Resources\PembatalanResource;
use App\Models\Pembatalan;
use App\Services\ReservasiService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PembatalanController extends Controller
{
    /**
     * Daftar pengajuan pembatalan (petugas). Filter: status. Pagination: per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::enum(StatusPembatalan::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return PembatalanResource::collection(
            Pembatalan::query()
                ->with(['reservasi.tamu', 'pemroses'])
                ->when($filter['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
                ->latest('id')
                ->paginate($filter['per_page'] ?? 15),
        );
    }

    public function proses(ProsesPembatalanRequest $request, Pembatalan $pembatalan, ReservasiService $service): PembatalanResource
    {
        $data = $request->validated();

        $pembatalan = $service->prosesPembatalan(
            $pembatalan,
            StatusPembatalan::from($data['status']),
            $request->user()->id,
            $data['catatan_petugas'] ?? null,
        );

        return new PembatalanResource($pembatalan->load(['reservasi.tamu', 'pemroses']));
    }
}
