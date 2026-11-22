<?php

namespace App\Http\Requests;

use App\Models\Fasilitas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FasilitasRequest extends FormRequest
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
        /** @var Fasilitas|null $fasilitas */
        $fasilitas = $this->route('fasilitas');

        return [
            'nama' => ['required', 'string', 'max:50', Rule::unique('fasilitas', 'nama')->ignore($fasilitas?->id)],
        ];
    }
}
