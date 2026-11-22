<?php

namespace App\Http\Requests;

use App\Enums\Peran;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User|null $pengguna */
        $pengguna = $this->route('pengguna');
        $baru = $pengguna === null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna?->id)],
            'password' => [$baru ? 'required' : 'nullable', 'string', Password::min(8)],
            'peran' => ['required', Rule::in(array_map(fn (Peran $p) => $p->value, Peran::staf()))],
        ];
    }
}
