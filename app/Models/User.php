<?php

namespace App\Models;

use App\Enums\Peran;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// 'role' sengaja TIDAK di-fillable agar tidak bisa diisi lewat form (mencegah user jadi admin).
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Peran::class,
        ];
    }

    public function adalahStaf(): bool
    {
        return $this->role?->adalahStaf() ?? false;
    }

    /** Data tamu (NIK, no HP, dst.) milik akun ini. */
    public function tamu(): HasOne
    {
        return $this->hasOne(Tamu::class);
    }
}
