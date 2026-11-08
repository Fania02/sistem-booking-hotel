<?php

namespace App\Enums\Concerns;

/**
 * Mengubah enum menjadi pasangan value + label supaya mudah dipakai frontend
 * (misalnya untuk dropdown atau badge status).
 */
trait SebagaiOpsi
{
    /**
     * @return array{value: string, label: string}
     */
    public function opsi(): array
    {
        return ['value' => $this->value, 'label' => $this->label()];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function daftarOpsi(): array
    {
        return array_map(fn (self $case): array => $case->opsi(), self::cases());
    }
}
