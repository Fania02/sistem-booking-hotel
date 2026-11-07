<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'reservasi_id',
    'waktu_checkin',
    'diproses_checkin_oleh',
    'waktu_checkout',
    'diproses_checkout_oleh',
    'catatan',
])]
class CheckinCheckout extends Model
{
    protected $table = 'checkin_checkout';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_checkin' => 'datetime',
            'waktu_checkout' => 'datetime',
        ];
    }

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class);
    }

    public function petugasCheckin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_checkin_oleh');
    }

    public function petugasCheckout(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_checkout_oleh');
    }
}
