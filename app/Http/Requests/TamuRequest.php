<?php

namespace App\Http\Requests;

use App\Rules\Nik;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TamuRequest extends FormRequest
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
            'nama' => ['required', 'string', 'max:255'],
            'nik' => [
                'required', 'string', new Nik,
                Rule::unique('tamu', 'nik')->ignore($this->route('tamu')),
            ],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
