<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\UbahStatusReservasiRequest;
use App\Http\Resources\ReservasiResource;
use App\Models\Reservasi;
use App\Services\ReservasiService;

class ReservasiStatusController extends Controller
{
    public function __invoke(UbahStatusReservasiRequest $request, Reservasi $reservasi, ReservasiService $service): ReservasiResource
    {
        $data = $request->validated();

        $reservasi = $service->ubahStatus(
            $reservasi,
            StatusReservasi::from($data['status']),
            $data,
            $request->user()?->id,
        );

        return new ReservasiResource(
            $reservasi->load(['tamu', 'detail.kamar.tipeKamar', 'checkinCheckout', 'pembatalan']),
        );
    }
}
