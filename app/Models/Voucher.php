<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'type',
        'value',
        'expires_at',
        'is_active',
    ];
}

