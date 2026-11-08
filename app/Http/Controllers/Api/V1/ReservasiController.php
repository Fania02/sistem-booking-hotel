<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservasiRequest;
use App\Http\Resources\ReservasiResource;
use App\Models\Reservasi;
use App\Services\ReservasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ReservasiController extends Controller
{
    /** @var list<string> */
    private const RELASI = ['tamu', 'detail.kamar.tipeKamar', 'checkinCheckout', 'pembatalan'];

    public function __construct(private ReservasiService $service) {}

    /**
     * Daftar reservasi. Filter: search (kode booking / nama tamu), status, tamu_id,
     * dari & sampai (rentang tgl_checkin). Pagination: per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filter = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(StatusReservasi::class)],
            'tamu_id' => ['nullable', 'integer'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $reservasi = Reservasi::query()
            ->with(self::RELASI)
            ->when($filter['search'] ?? null, function ($q, $cari): void {
                $q->where(fn ($w) => $w
                    ->where('kode_booking', 'like', "%{$cari}%")
                    ->orWhereHas('tamu', fn ($t) => $t->where('nama', 'like', "%{$cari}%")));
            })
            ->when($filter['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filter['tamu_id'] ?? null, fn ($q, $id) => $q->where('tamu_id', $id))
            ->when($filter['dari'] ?? null, fn ($q, $tgl) => $q->whereDate('tgl_checkin', '>=', $tgl))
            ->when($filter['sampai'] ?? null, fn ($q, $tgl) => $q->whereDate('tgl_checkin', '<=', $tgl))
            ->latest('id')
            ->paginate($filter['per_page'] ?? 15);

        return ReservasiResource::collection($reservasi);
    }

    public function store(ReservasiRequest $request): JsonResponse
    {
        $reservasi = $this->service->buat($request->validated(), $request->user()?->id);

        return (new ReservasiResource($reservasi->load(self::RELASI)))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Reservasi $reservasi): ReservasiResource
    {
        return new ReservasiResource($reservasi->load(self::RELASI));
    }

    public function update(ReservasiRequest $request, Reservasi $reservasi): ReservasiResource
    {
        $reservasi = $this->service->ubah($reservasi, $request->validated());

        return new ReservasiResource($reservasi->load(self::RELASI));
    }

    /**
     * Hanya reservasi berstatus batal yang boleh dihapus; selain itu ubah statusnya ke batal dulu.
     */
    public function destroy(Reservasi $reservasi): Response|JsonResponse
    {
        if ($reservasi->status !== StatusReservasi::Batal) {
            return response()->json([
                'message' => 'Hanya reservasi yang dibatalkan yang dapat dihapus.',
            ], Response::HTTP_CONFLICT);
        }

        $reservasi->delete();

        return response()->noContent();
    }
}
