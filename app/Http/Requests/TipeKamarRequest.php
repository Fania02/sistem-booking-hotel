<?php

namespace App\Http\Requests;

use App\Models\TipeKamar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipeKamarRequest extends FormRequest
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
        /** @var TipeKamar|null $tipe */
        $tipe = $this->route('tipe_kamar');

        return [
            'nama_tipe' => [
                'required', 'string', 'max:50',
                Rule::unique('tipe_kamar', 'nama_tipe')->ignore($tipe?->id)->whereNull('deleted_at'),
            ],
            'harga_per_malam' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:20'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'fasilitas_ids' => ['nullable', 'array'],
            'fasilitas_ids.*' => ['integer', 'distinct', 'exists:fasilitas,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['nama_tipe' => 'nama tipe', 'harga_per_malam' => 'harga per malam'];
    }
}
