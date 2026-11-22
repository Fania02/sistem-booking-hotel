<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusPembatalan;
use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\AjukanPembatalanRequest;
use App\Http\Requests\PembayaranRequest;
use App\Http\Resources\PembatalanResource;
use App\Http\Resources\PembayaranResource;
use App\Http\Resources\ReservasiResource;
use App\Models\Reservasi;
use App\Services\PembayaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Endpoint untuk tamu yang login: riwayat reservasi sendiri, upload bukti bayar, ajukan pembatalan.
 * Reservasi milik tamu lain dijawab 404 (bukan 403) agar keberadaannya tidak bocor.
 */
class SayaController extends Controller
{
    /** @var list<string> */
    private const RELASI = ['detail.kamar.tipeKamar', 'pembatalan', 'pembayaran'];

    public function reservasi(Request $request): AnonymousResourceCollection
    {
        return ReservasiResource::collection(
            Reservasi::query()
                ->milikUser($request->user()->id)
                ->with(self::RELASI)
                ->latest('id')
                ->paginate(10),
        );
    }

    public function bayar(PembayaranRequest $request, Reservasi $reservasi, PembayaranService $service): JsonResponse
    {
        $this->pastikanMilik($request, $reservasi);

        $pembayaran = $service->catat($reservasi, $request->validated(), $request->user());

        return (new PembayaranResource($pembayaran))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function ajukanPembatalan(AjukanPembatalanRequest $request, Reservasi $reservasi): JsonResponse
    {
        $this->pastikanMilik($request, $reservasi);

        if (! in_array($reservasi->status, [StatusReservasi::Menunggu, StatusReservasi::Dikonfirmasi], true)) {
            throw ValidationException::withMessages([
                'status' => "Reservasi berstatus {$reservasi->status->label()} tidak dapat dibatalkan.",
            ]);
        }

        if ($reservasi->pembatalan()->where('status', StatusPembatalan::Diajukan->value)->exists()) {
            throw ValidationException::withMessages(['status' => 'Pengajuan pembatalan sudah dikirim dan sedang diproses.']);
        }

        $pembatalan = $reservasi->pembatalan()->create([
            'alasan' => $request->validated('alasan'),
            'tgl_pengajuan' => now(),
            'status' => StatusPembatalan::Diajukan,
        ]);

        return (new PembatalanResource($pembatalan))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    private function pastikanMilik(Request $request, Reservasi $reservasi): void
    {
        abort_unless(
            Reservasi::query()->milikUser($request->user()->id)->whereKey($reservasi->getKey())->exists(),
            Response::HTTP_NOT_FOUND,
        );
    }
}
