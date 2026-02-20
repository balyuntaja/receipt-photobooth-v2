<?php

namespace App\Models;

use App\Enums\TransactionStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_id',
        'session_id',
        'owner_user_id',
        'device_id',
        'amount',
        'gross_amount',
        'platform_fee',
        'owner_amount',
        'paid_out_at',
        'discount',
        'status',
        'payment_type',
        'payload',
        'type',
    ];

    protected $casts = [
        'status' => TransactionStatusEnum::class,
        'payload' => 'array',
        'paid_out_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(BoothSession::class, 'session_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
