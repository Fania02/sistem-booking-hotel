<?php

namespace App\Http\Requests;

use App\Enums\StatusKamar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KamarRequest extends FormRequest
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
            'tipe_kamar_id' => ['required', 'integer', Rule::exists('tipe_kamar', 'id')->whereNull('deleted_at')],
            'nomor_kamar' => [
                'required', 'string', 'max:10',
                Rule::unique('kamar', 'nomor_kamar')->ignore($this->route('kamar')),
            ],
            'lantai' => ['required', 'integer', 'min:0', 'max:100'],
            'status' => ['required', Rule::enum(StatusKamar::class)],
        ];
    }
}
