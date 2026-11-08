<?php

namespace App\Http\Resources;

use App\Models\DetailReservasi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DetailReservasi */
class DetailReservasiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kamar' => new KamarResource($this->whenLoaded('kamar')),
            'harga_per_malam' => (float) $this->harga_per_malam,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
