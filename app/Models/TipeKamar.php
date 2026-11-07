<?php

namespace App\Models;

use Database\Factories\TipeKamarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nama_tipe', 'harga_per_malam', 'kapasitas', 'deskripsi'])]
class TipeKamar extends Model
{
    /** @use HasFactory<TipeKamarFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'tipe_kamar';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_per_malam' => 'decimal:2',
            'kapasitas' => 'integer',
        ];
    }

    public function kamar(): HasMany
    {
        return $this->hasMany(Kamar::class);
    }
}
