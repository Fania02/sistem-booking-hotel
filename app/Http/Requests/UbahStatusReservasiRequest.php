<?php

namespace App\Http\Requests;

use App\Enums\StatusReservasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbahStatusReservasiRequest extends FormRequest
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
            'status' => ['required', Rule::enum(StatusReservasi::class)],
            'waktu' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'alasan' => ['required_if:status,batal', 'nullable', 'string', 'max:1000'],
        ];
    }
}
