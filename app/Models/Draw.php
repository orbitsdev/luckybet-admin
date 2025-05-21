<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\Result;
use App\Models\BetRatio;
use App\Models\GameType;
use App\Models\Schedule;
use App\Models\LowWinNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Draw extends Model
{

    use HasFactory;

    protected $fillable = [
        'draw_date',
        'draw_time',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'is_active' => 'boolean',
        'draw_date' => 'date',
        'draw_time' => 'string',
    ];

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    

    public function result(): HasOne
    {
        return $this->hasOne(Result::class);
    }

    public function betRatios(){
        return $this->hasMany(BetRatio::class);
    }

    /**
     * Get all low win numbers for this draw
     */
    public function lowWinNumbers(): HasMany
    {
        return $this->hasMany(LowWinNumber::class);
    }

}
