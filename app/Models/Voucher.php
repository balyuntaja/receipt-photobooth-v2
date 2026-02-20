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
        'quota',
        'expires_at',
        'is_active',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

