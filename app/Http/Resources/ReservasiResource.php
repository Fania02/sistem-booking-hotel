<?php

namespace App\Http\Resources;

use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Reservasi */
class ReservasiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_booking' => $this->kode_booking,
            'status' => $this->status->opsi(),
            'status_pembayaran' => $this->status_pembayaran->opsi(),
            'sumber' => $this->sumber->opsi(),
            'tgl_checkin' => $this->tgl_checkin->toDateString(),
            'tgl_checkout' => $this->tgl_checkout->toDateString(),
            'jumlah_malam' => $this->jumlahMalam(),
            'total_harga' => (float) $this->total_harga,
            'tamu' => new TamuResource($this->whenLoaded('tamu')),
            'kamar' => DetailReservasiResource::collection($this->whenLoaded('detail')),
            'checkin_checkout' => $this->whenLoaded('checkinCheckout', fn () => $this->checkinCheckout === null ? null : [
                'waktu_checkin' => $this->checkinCheckout->waktu_checkin?->toIso8601String(),
                'waktu_checkout' => $this->checkinCheckout->waktu_checkout?->toIso8601String(),
                'catatan' => $this->checkinCheckout->catatan,
            ]),
            'pembatalan' => $this->whenLoaded('pembatalan', fn () => $this->pembatalan->last() === null ? null : [
                'alasan' => $this->pembatalan->last()->alasan,
                'tgl_diproses' => $this->pembatalan->last()->tgl_diproses?->toIso8601String(),
            ]),
            'dibuat_pada' => $this->created_at?->toIso8601String(),
            'status_berikutnya' => array_map(fn ($s) => $s->opsi(), $this->status->transisiTersedia()),
        ];
    }
}
