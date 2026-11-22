<?php

namespace App\Models;

use App\Enums\Peran;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'nik', 'no_hp'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'role'              => Peran::class,
        ];
    }

    /** Apakah user ini termasuk staf hotel (admin / resepsionis)? */
    public function adalahStaf(): bool
    {
        return in_array($this->role, Peran::staf(), true);
    }

    public function adalahAdmin(): bool
    {
        return $this->role === Peran::Admin;
    }
}
