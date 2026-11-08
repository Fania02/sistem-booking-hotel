<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\TamuRequest;
use App\Http\Resources\TamuResource;
use App\Models\Tamu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TamuController extends Controller
{
    /**
     * Daftar tamu. Filter: search (nama, NIK, no. HP). Pagination: per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filter = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $tamu = Tamu::query()
            ->withCount('reservasi')
            ->when($filter['search'] ?? null, function ($q, $cari): void {
                $q->where(fn ($w) => $w
                    ->where('nama', 'like', "%{$cari}%")
                    ->orWhere('nik', 'like', "%{$cari}%")
                    ->orWhere('no_hp', 'like', "%{$cari}%"));
            })
            ->orderBy('nama')
            ->paginate($filter['per_page'] ?? 15);

        return TamuResource::collection($tamu);
    }

    public function store(TamuRequest $request): JsonResponse
    {
        $tamu = Tamu::create($request->validated());

        return (new TamuResource($tamu))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tamu $tamu): TamuResource
    {
        return new TamuResource($tamu->loadCount('reservasi'));
    }

    public function update(TamuRequest $request, Tamu $tamu): TamuResource
    {
        $tamu->update($request->validated());

        return new TamuResource($tamu->loadCount('reservasi'));
    }

    public function destroy(Tamu $tamu): Response|JsonResponse
    {
        if ($tamu->reservasi()->whereIn('status', StatusReservasi::nilaiAktif())->exists()) {
            return response()->json([
                'message' => 'Tamu masih memiliki reservasi aktif sehingga tidak dapat dihapus.',
            ], Response::HTTP_CONFLICT);
        }

        $tamu->delete();

        return response()->noContent();
    }
}
