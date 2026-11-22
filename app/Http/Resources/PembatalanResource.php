<?php

namespace App\Http\Resources;

use App\Models\Pembatalan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pembatalan */
class PembatalanResource extends JsonResource
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
            'tamu' => $this->whenLoaded('reservasi', fn () => $this->reservasi->tamu?->nama),
            'alasan' => $this->alasan,
            'status' => $this->status->opsi(),
            'tgl_pengajuan' => $this->tgl_pengajuan?->toIso8601String(),
            'tgl_diproses' => $this->tgl_diproses?->toIso8601String(),
            'diproses_oleh' => $this->whenLoaded('pemroses', fn () => $this->pemroses?->name),
            'catatan_petugas' => $this->catatan_petugas,
        ];
    }
}
