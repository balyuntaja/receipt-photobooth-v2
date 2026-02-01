<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'preview_image',
        'frame_file',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_frame')
            ->withPivot('is_active')
            ->withTimestamps();
    }

}

