<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\User;
use App\Models\BetRatio;
use App\Models\TallySheet;
use App\Models\LowWinNumber;
use App\Models\WinningAmount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
    ];
    use HasFactory;
    
    protected $fillable = [
        'name',      // Branch name
        'address',   // Location address
        'is_active', // Show/Hide from dropdown
    ];
 
    
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }



    public function betRatios()
    {
        return $this->hasMany(BetRatio::class);
    }

    public function lowWinNumbers()
    {
        return $this->hasMany(LowWinNumber::class);
    }

    public function winningAmounts()
    {
        return $this->hasMany(WinningAmount::class);
    }
}
