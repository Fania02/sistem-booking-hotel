<?php

namespace App\Models;

use App\Enums\StatusPembatalan;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reservasi_id',
    'alasan',
    'tgl_pengajuan',
    'status',
    'diproses_oleh',
    'tgl_diproses',
    'catatan_petugas',
])]
class Pembatalan extends Model
{
    protected $table = 'pembatalan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_pengajuan' => 'datetime',
            'tgl_diproses' => 'datetime',
            'status' => StatusPembatalan::class,
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
