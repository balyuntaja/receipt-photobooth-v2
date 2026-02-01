<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
        'cover_image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sessions()
    {
        return $this->hasMany(BoothSession::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function setting()
    {
        return $this->hasOne(ProjectSetting::class);
    }

    public function frames()
    {
        return $this->belongsToMany(Frame::class, 'project_frame')
            ->withPivot('is_active')
            ->withTimestamps();
    }



}
