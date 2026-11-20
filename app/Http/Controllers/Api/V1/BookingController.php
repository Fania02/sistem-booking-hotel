<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SumberReservasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Reservasi;
use App\Models\Tamu;
use App\Services\ReservasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(private ReservasiService $service) {}

    /**
     * Pemesanan publik: mencari tamu berdasarkan NIK (atau membuat baru), lalu membuat reservasi.
     * Data tamu yang sudah ada tidak ditimpa, dan respons tidak membocorkan data tamu.
     */
    public function store(BookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $reservasi = DB::transaction(function () use ($data, $request): Reservasi {
            $tamu = Tamu::withTrashed()->firstOrCreate(
                ['nik' => $data['nik']],
                ['nama' => $data['nama'], 'no_hp' => $data['no_hp'], 'alamat' => $data['alamat'] ?? null],
            );

            if ($tamu->trashed()) {
                $tamu->restore();
            }

            return $this->service->buat([
                'tamu_id' => $tamu->id,
                'tgl_checkin' => $data['tgl_checkin'],
                'tgl_checkout' => $data['tgl_checkout'],
                'sumber' => SumberReservasi::Online->value,
                'kamar_ids' => $data['kamar_ids'],
            ], $request->user()?->id);
        });

        return (new BookingResource($reservasi->load('detail.kamar.tipeKamar')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Cek reservasi dengan kode booking + NIK pemesan.
     */
    public function show(Request $request, string $kode): BookingResource
    {
        $nik = $request->validate(['nik' => ['required', 'string']])['nik'];

        $reservasi = Reservasi::query()
            ->with('detail.kamar.tipeKamar')
            ->where('kode_booking', $kode)
            ->whereHas('tamu', fn ($q) => $q->where('nik', $nik))
            ->firstOrFail();

        return new BookingResource($reservasi);
    }
}
