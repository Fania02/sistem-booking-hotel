<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum MetodePembayaran: string
{
    use SebagaiOpsi;

    case Tunai = 'tunai';
    case Transfer = 'transfer';
    case Qris = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Transfer => 'Transfer',
            self::Qris => 'QRIS',
        };
    }
}
