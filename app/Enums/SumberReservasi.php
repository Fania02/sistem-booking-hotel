<?php

namespace App\Enums;

enum SumberReservasi: string
{
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
