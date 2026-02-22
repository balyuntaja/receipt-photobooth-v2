<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'filter_id',
        'frame_id',
        'copy_count',
        'retake_count',
    ];

    public function session()
    {
        return $this->belongsTo(BoothSession::class, 'session_id', 'id');
    }
}
