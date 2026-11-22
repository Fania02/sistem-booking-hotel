<?php

namespace App\Http\Requests;

use App\Enums\MetodePembayaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Petugas boleh semua metode (bukti opsional). Tamu hanya transfer/QRIS dan wajib melampirkan bukti.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staf = (bool) $this->user()?->adalahStaf();
        $metode = $staf ? MetodePembayaran::cases() : [MetodePembayaran::Transfer, MetodePembayaran::Qris];

        return [
            'jumlah_bayar' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'metode' => ['required', Rule::in(array_map(fn ($m) => $m->value, $metode))],
            'bukti_bayar' => [$staf ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'tgl_bayar' => ['nullable', 'date', 'before_or_equal:now'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
