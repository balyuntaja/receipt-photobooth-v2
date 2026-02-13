<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover_image',
        'welcome_background_color',
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

    /**
     * Get the welcome screen components for this project.
     */
    public function welcomeScreenComponents()
    {
        return $this->hasMany(WelcomeScreenComponent::class)->ordered();
    }

    /**
     * Get the background component for welcome screen (only one allowed).
     */
    public function welcomeScreenBackground()
    {
        return $this->hasOne(WelcomeScreenComponent::class)
            ->where('type', 'background')
            ->latest();
    }
}
