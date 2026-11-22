<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusVerifikasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\PembayaranRequest;
use App\Http\Requests\VerifikasiPembayaranRequest;
use App\Http\Resources\PembayaranResource;
use App\Models\Pembayaran;
use App\Models\Reservasi;
use App\Services\PembayaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    public function __construct(private PembayaranService $service) {}

    /**
     * Semua pembayaran (petugas). Filter: status, reservasi_id. Pagination: per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::enum(StatusVerifikasi::class)],
            'reservasi_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return PembayaranResource::collection(
            Pembayaran::query()
                ->with(['reservasi.tamu', 'verifikator'])
                ->when($filter['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
                ->when($filter['reservasi_id'] ?? null, fn ($q, $id) => $q->where('reservasi_id', $id))
                ->latest('id')
                ->paginate($filter['per_page'] ?? 15),
        );
    }

    public function perReservasi(Reservasi $reservasi): AnonymousResourceCollection
    {
        return PembayaranResource::collection($reservasi->pembayaran()->with('verifikator')->latest('id')->get());
    }

    public function store(PembayaranRequest $request, Reservasi $reservasi): JsonResponse
    {
        $pembayaran = $this->service->catat($reservasi, $request->validated(), $request->user());

        return (new PembayaranResource($pembayaran->load('verifikator')))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function verifikasi(VerifikasiPembayaranRequest $request, Pembayaran $pembayaran): PembayaranResource
    {
        $data = $request->validated();

        $pembayaran = $this->service->verifikasi(
            $pembayaran,
            StatusVerifikasi::from($data['status']),
            $request->user(),
            $data['catatan'] ?? null,
        );

        return new PembayaranResource($pembayaran->load(['reservasi.tamu', 'verifikator']));
    }
}
