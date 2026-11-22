<?php

namespace App\Http\Resources;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Fasilitas */
class FasilitasResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'jumlah_tipe_kamar' => $this->whenCounted('tipeKamar'),
        ];
    }
}
