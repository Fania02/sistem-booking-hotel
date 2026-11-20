<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Aturan NIK: tepat 16 digit angka dan 2 digit pertama adalah kode provinsi yang valid.
 * Keunikan NIK dicek terpisah (lihat TamuRequest).
 */
class Nik implements ValidationRule
{
    private const KODE_PROVINSI = [
        11, 12, 13, 14, 15, 16, 17, 18, 19, 21,
        31, 32, 33, 34, 35, 36,
        51, 52, 53,
        61, 62, 63, 64, 65,
        71, 72, 73, 74, 75, 76,
        81, 82,
        91, 92, 93, 94, 95, 96,
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/^\d{16}$/', $value) !== 1) {
            $fail('NIK harus terdiri dari 16 digit angka.');

            return;
        }

        if (! in_array((int) substr($value, 0, 2), self::KODE_PROVINSI, true)) {
            $fail('NIK tidak valid: kode provinsi (2 digit pertama) tidak dikenal.');
        }
    }
}
