<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyEarning extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'total_gross',
        'total_fee',
        'total_net',
        'payout_status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public const PAYOUT_STATUS_PENDING = 'pending';
    public const PAYOUT_STATUS_PAID = 'paid';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('payout_status', self::PAYOUT_STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('payout_status', self::PAYOUT_STATUS_PAID);
    }
}
