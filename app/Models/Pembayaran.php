<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reservasi_id',
    'jumlah_bayar',
    'metode',
    'bukti_bayar',
    'tgl_bayar',
    'status',
    'diverifikasi_oleh',
    'catatan',
])]
class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'decimal:2',
            'tgl_bayar' => 'datetime',
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
