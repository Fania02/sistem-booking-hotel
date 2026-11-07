<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['reservasi_id', 'kamar_id', 'harga_per_malam', 'subtotal'])]
class DetailReservasi extends Model
{
    protected $table = 'detail_reservasi';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_per_malam' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }
}
