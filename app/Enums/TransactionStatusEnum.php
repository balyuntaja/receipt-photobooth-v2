<?php

namespace App\Enums;

enum TransactionStatusEnum: string
{
    case PAID    = 'paid';
    case PENDING = 'pending';
    case FAILED  = 'failed';
    case FREE    = 'free';

    public function label(): string
    {
        return match ($this) {
            self::PAID    => 'Berhasil',
            self::PENDING => 'Pending',
            self::FAILED  => 'Gagal',
            self::FREE    => 'Gratis',
        };
    }
}
