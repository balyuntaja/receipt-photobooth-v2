<?php

namespace App\Models;

use App\Enums\DeviceTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'model',
    ];

    protected $casts = [
        'type' => DeviceTypeEnum::class,
    ];

    public function sessions()
    {
        return $this->belongsToMany(BoothSession::class)
            ->withTimestamps();
    }
}
