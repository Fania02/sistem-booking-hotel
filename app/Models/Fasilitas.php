<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nama'])]
class Fasilitas extends Model
{
    protected $table = 'fasilitas';

    public function tipeKamar(): BelongsToMany
    {
        return $this->belongsToMany(TipeKamar::class, 'fasilitas_tipe_kamar')->withTimestamps();
    }
}
