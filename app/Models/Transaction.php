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
        'session_id',
        'amount',
        'discount',
        'status',
    ];

    protected $casts = [
        'status' => TransactionStatusEnum::class,
    ];

    public function session()
    {
        return $this->belongsTo(BoothSession::class, 'session_id', 'id');
    }
}
