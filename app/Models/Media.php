<?php

namespace App\Models;

use App\Enums\MediaTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'type',
        'file_path',
    ];

    protected $casts = [
        'type' => MediaTypeEnum::class,
    ];

    public function session()
    {
        return $this->belongsTo(BoothSession::class, 'session_id', 'id');
    }
}
