<?php

namespace App\Enums;

enum MetodePembayaran: string
{
    case TransferBank  = 'transfer_bank';
    case KartuKredit   = 'kartu_kredit';
    case Tunai         = 'tunai';
    case DompetDigital = 'dompet_digital';

    public function label(): string
    {
        return match ($this) {
            self::TransferBank  => 'Transfer Bank',
            self::KartuKredit   => 'Kartu Kredit',
            self::Tunai         => 'Tunai',
            self::DompetDigital => 'Dompet Digital',
        };
    }
}
