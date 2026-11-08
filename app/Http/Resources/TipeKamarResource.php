<?php

namespace App\Http\Resources;

use App\Models\TipeKamar;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TipeKamar */
class TipeKamarResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_tipe' => $this->nama_tipe,
            'harga_per_malam' => (float) $this->harga_per_malam,
            'kapasitas' => $this->kapasitas,
            'deskripsi' => $this->deskripsi,
        ];
    }
}
