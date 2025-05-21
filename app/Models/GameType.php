<?php

namespace App\Models;

use App\Models\BetRatio;
use App\Models\WinningAmount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GameType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the bets for this game type
     */
    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    /**
     * Get the draws for this game type
     */
    public function draws(): HasMany
    {
        return $this->hasMany(Draw::class);
    }

    public function winningAmounts(){
        return $this->hasMany(WinningAmount::class);
    }

    public function betRatios(){
        return $this->hasMany(BetRatio::class);
    }

}

