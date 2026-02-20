<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'package_name',
        'period',
        'status',
        'started_at',
        'ends_at',
        'token',
        'device_info',
        'source',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif' && $this->ends_at->isFuture();
    }

    public function regenerateToken(): string
    {
        $token = Str::random(32);
        $this->update(['token' => $token]);
        return $token;
    }
}
