<?php

namespace App\Http\Requests;

use App\Enums\SumberReservasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Saat membuat (POST) semua field wajib; saat mengubah (PUT/PATCH) cukup kirim yang berubah.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $wajib = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'tamu_id' => [$wajib, 'integer', Rule::exists('tamu', 'id')->whereNull('deleted_at')],
            'tgl_checkin' => [$wajib, 'date_format:Y-m-d', $this->isMethod('POST') ? 'after_or_equal:today' : 'date_format:Y-m-d'],
            'tgl_checkout' => [$wajib, 'date_format:Y-m-d', 'after:tgl_checkin'],
            'sumber' => [$wajib, Rule::enum(SumberReservasi::class)],
            'kamar_ids' => [$wajib, 'array', 'min:1'],
            'kamar_ids.*' => ['integer', 'distinct', Rule::exists('kamar', 'id')->whereNull('deleted_at')],
        ];
    }
}
