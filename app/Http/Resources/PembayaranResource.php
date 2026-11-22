<?php

namespace App\Http\Resources;

use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Pembayaran */
class PembayaranResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reservasi_id' => $this->reservasi_id,
            'kode_booking' => $this->whenLoaded('reservasi', fn () => $this->reservasi->kode_booking),
            'jumlah_bayar' => (float) $this->jumlah_bayar,
            'metode' => $this->metode->opsi(),
            'bukti_bayar_url' => $this->bukti_bayar ? Storage::disk('public')->url($this->bukti_bayar) : null,
            'tgl_bayar' => $this->tgl_bayar?->toIso8601String(),
            'status' => $this->status->opsi(),
            'diverifikasi_oleh' => $this->whenLoaded('verifikator', fn () => $this->verifikator?->name),
            'catatan' => $this->catatan,
        ];
    }
}
