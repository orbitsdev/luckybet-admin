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
    use HasFactory;

    protected $fillable = [
        'draw_date', 
        'draw_time', 
        'game_type_id', // Needed for multi-game lottery system
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
    
    // Schedule relationship removed as per new structure
    
    public function gameType(): BelongsTo
    {
        return $this->belongsTo(GameType::class);
    }
    
    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }
}
