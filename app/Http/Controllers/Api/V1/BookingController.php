<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservasiResource;
use App\Models\Tamu;
use App\Services\ReservasiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Booking dari halaman publik (tamu login maupun belum login). */
class BookingController extends Controller
{
    public function __construct(private ReservasiService $service) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'digits:16'],
            'no_hp' => ['required', 'string', 'max:15'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'tgl_checkin' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'tgl_checkout' => ['required', 'date_format:Y-m-d', 'after:tgl_checkin'],
            'kamar_ids' => ['required', 'array', 'min:1'],
            'kamar_ids.*' => ['integer', 'distinct', 'exists:kamar,id'],
        ]);

        $reservasi = DB::transaction(function () use ($data, $request) {
            // Tamu yang sudah login memakai data tamu akunnya; selain itu cocokkan lewat NIK.
            $tamu = $request->user()?->tamu
                ?? Tamu::firstOrCreate(
                    ['nik' => $data['nik']],
                    ['nama' => $data['nama'], 'no_hp' => $data['no_hp'], 'alamat' => $data['alamat'] ?? ''],
                );

            return $this->service->buat([
                'tamu_id' => $tamu->id,
                'tgl_checkin' => $data['tgl_checkin'],
                'tgl_checkout' => $data['tgl_checkout'],
                'sumber' => 'online',
                'kamar_ids' => $data['kamar_ids'],
            ]);
        });

        return (new ReservasiResource($reservasi->load('tamu', 'detail.kamar.tipeKamar')))
            ->response()
            ->setStatusCode(201);
    }
}
