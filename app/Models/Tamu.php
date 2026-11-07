<?php

namespace App\Models;

use Database\Factories\TamuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['user_id', 'nama', 'nik', 'no_hp', 'alamat'])]
class Tamu extends Model
{
    /** @use HasFactory<TamuFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tamu';

    /**
     * Akun login tamu. Kosong untuk tamu walk-in yang tidak punya akun.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservasi(): HasMany
    {
        return $this->hasMany(Reservasi::class);
    }
}
