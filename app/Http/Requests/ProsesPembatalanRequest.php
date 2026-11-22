<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProsesPembatalanRequest extends FormRequest
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
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'catatan_petugas' => ['nullable', 'string', 'max:255', 'required_if:status,ditolak'],
        ];
    }
}
