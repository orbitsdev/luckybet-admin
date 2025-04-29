<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\Teller;
use App\Models\TallySheet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Location extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'address', 'coordinator_id', 'is_active',
    ];
    public function tellers()
    {
        return $this->hasMany(User::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
