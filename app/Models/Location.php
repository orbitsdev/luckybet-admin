<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\User;
use App\Models\TallySheet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',      // Branch name
        'address',   // Location address
        'is_active', // Show/Hide from dropdown
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
    
    public function tallySheets()
    {
        return $this->hasMany(TallySheet::class);
    }
}
