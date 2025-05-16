<?php

namespace App\Models;

use App\Models\Draw;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',      // e.g., "2:00 PM"
        'draw_time', // System time format
        'is_active', // Show/Hide from dropdown
    ];

    protected $casts = [
        'draw_time' => 'string',
        'is_active' => 'boolean',
    ];

    
}
