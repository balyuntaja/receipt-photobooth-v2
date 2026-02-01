<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'image_path',
        'printed_at',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(BoothSession::class);
    }
}
