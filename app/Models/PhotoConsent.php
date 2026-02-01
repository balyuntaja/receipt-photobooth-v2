<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhotoConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'is_allowed',
        'responded_at',
    ];

    protected $casts = [
        'is_allowed'   => 'boolean',
        'responded_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(BoothSession::class);
    }
}
