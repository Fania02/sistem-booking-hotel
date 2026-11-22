<?php

namespace App\Http\Resources;

use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ringkasan reservasi untuk halaman publik. Sengaja tanpa data tamu (NIK, nama, no. HP).
 *
 * @mixin Reservasi
 */
class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kode_booking' => $this->kode_booking,
            'status' => $this->status->opsi(),
            'status_pembayaran' => $this->status_pembayaran->opsi(),
            'tgl_checkin' => $this->tgl_checkin->toDateString(),
            'tgl_checkout' => $this->tgl_checkout->toDateString(),
            'jumlah_malam' => $this->jumlahMalam(),
            'total_harga' => (float) $this->total_harga,
            'kamar' => DetailReservasiResource::collection($this->whenLoaded('detail')),
        ];
    }
}
