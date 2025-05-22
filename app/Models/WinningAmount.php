<?php

namespace App\Models;

use App\Models\GameType;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;

class WinningAmount extends Model
{

     protected $fillable = [
        'game_type_id',
        'amount',
        'winning_amount',
    
    ];

    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }
    
}
