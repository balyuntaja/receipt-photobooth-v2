<?php

namespace App\Enums;

enum TransactionStatusEnum: string
{
    case PAID   = 'paid';
    case FAILED = 'failed';
    case FREE   = 'free';

    public function label(): string
    {
        return match ($this) {
            self::PAID   => 'Berhasil',
            self::FAILED => 'Gagal',
            self::FREE   => 'Gratis',
        };
    }
}
