<?php

namespace App\Enums;

use App\Enums\Concerns\SebagaiOpsi;

enum SumberReservasi: string
{
    use SebagaiOpsi;

    case Online = 'online';
    case WalkIn = 'walk-in';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::WalkIn => 'Walk-in',
        };
    }
}
