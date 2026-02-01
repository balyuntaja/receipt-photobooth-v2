<?php

namespace App\Enums;

enum SessionStatusEnum: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case CANCELLED   = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'Berlangsung',
            self::COMPLETED   => 'Selesai',
            self::CANCELLED   => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'warning',
            self::COMPLETED   => 'success',
            self::CANCELLED   => 'danger',
        };
    }
}
