<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomeScreenComponent extends Model
{
    protected $fillable = [
        'project_id',
        'type',
        'content',
        'sort_order',
    ];

    protected $casts = [
        'content' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the project that owns this component.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope to order by sort_order ascending.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Check if this component is a background type.
     */
    public function isBackground(): bool
    {
        return $this->type === 'background';
    }

    /**
     * Check if this component is an image type.
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if this component is a text type.
     */
    public function isText(): bool
    {
        return $this->type === 'text';
    }
}
