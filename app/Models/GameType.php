<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
