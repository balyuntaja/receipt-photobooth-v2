<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'project_id',
        'price_per_session',
        'copies',
        'max_retakes',
        'auto_print',
    ];

}
