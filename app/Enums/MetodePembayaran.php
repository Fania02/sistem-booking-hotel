<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum MetodePembayaran: string
{
    use SebagaiOpsi;

    case Tunai = 'tunai';
    case Transfer = 'transfer';
    case Qris = 'qris';
    case TransferBank = 'transfer_bank';
    case KartuKredit = 'kartu_kredit';
    case DompetDigital = 'dompet_digital';

    public function label(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Transfer => 'Transfer',
            self::Qris => 'QRIS',
            self::TransferBank => 'Transfer Bank',
            self::KartuKredit => 'Kartu Kredit',
            self::DompetDigital => 'Dompet Digital',
        };
    }
}