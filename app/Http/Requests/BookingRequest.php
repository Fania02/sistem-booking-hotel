<?php

namespace App\Http\Requests;

use App\Rules\Nik;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Pemesanan oleh tamu dari halaman publik (tanpa login).
 */
class BookingRequest extends FormRequest
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
            'nik' => ['required', 'string', new Nik],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'tgl_checkin' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'tgl_checkout' => ['required', 'date_format:Y-m-d', 'after:tgl_checkin'],
            'kamar_ids' => ['required', 'array', 'min:1', 'max:5'],
            'kamar_ids.*' => ['integer', 'distinct', Rule::exists('kamar', 'id')->whereNull('deleted_at')],
        ];
    }
}
