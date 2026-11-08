<?php

namespace App\Http\Resources;

use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Kamar */
class KamarResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nomor_kamar' => $this->nomor_kamar,
            'lantai' => $this->lantai,
            'status' => $this->status->opsi(),
            'tipe_kamar_id' => $this->tipe_kamar_id,
            'tipe_kamar' => new TipeKamarResource($this->whenLoaded('tipeKamar')),
        ];
    }
}
