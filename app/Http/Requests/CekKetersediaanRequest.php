<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CekKetersediaanRequest extends FormRequest
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
            'tgl_checkin' => ['required', 'date_format:Y-m-d'],
            'tgl_checkout' => ['required', 'date_format:Y-m-d', 'after:tgl_checkin'],
            'tipe_kamar_id' => ['nullable', 'integer', Rule::exists('tipe_kamar', 'id')],
            'abaikan_reservasi_id' => ['nullable', 'integer'],
        ];
    }
}
