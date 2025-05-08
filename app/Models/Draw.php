<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\Result;
use App\Models\GameType;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Draw extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'is_open' => 'boolean',
    ];
    use HasFactory;

    protected $fillable = [
        'draw_date', 
        'draw_time', 
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'draw_date' => 'date',
        'draw_time' => 'string',
    ];

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    // Schedule is used only as a reference for draw_time
    // No direct relationship as per the documentation

    // Game type relationship removed as per documentation
    // Each result will have separate fields for different game types

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    
}
