<?php

namespace App\Models;

use App\Enums\SessionStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoothSession extends Model
{
    protected $table = 'booth_sessions';

    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'project_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_sec',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => SessionStatusEnum::class,
    ];

    /* ================= RELATIONS ================= */

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'session_id', 'id');
    }

    public function media()
    {
        return $this->hasMany(Media::class, 'session_id', 'id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }

    public function preference()
    {
        return $this->hasOne(SessionPreference::class);
    }

    public function devices()
    {
        return $this->belongsToMany(Device::class)
            ->withTimestamps();
    }

    public function consent()
    {
        return $this->hasOne(PhotoConsent::class);
    }
    public function frame()
    {
        return $this->belongsTo(Frame::class);
    }


}
