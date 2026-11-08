<?php

namespace App\Http\Resources;

use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Tamu */
class TamuResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'nik' => $this->nik,
            'no_hp' => $this->no_hp,
            'alamat' => $this->alamat,
            'jumlah_reservasi' => $this->whenCounted('reservasi'),
            'dibuat_pada' => $this->created_at?->toIso8601String(),
        ];
    }
}
