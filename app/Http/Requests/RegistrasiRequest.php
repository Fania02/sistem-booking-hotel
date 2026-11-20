<?php

namespace App\Http\Requests;

use App\Rules\Nik;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Pendaftaran akun tamu. Role selalu "tamu" dan tidak bisa dikirim dari form.
 */
class RegistrasiRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'nik' => ['required', 'string', new Nik],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
