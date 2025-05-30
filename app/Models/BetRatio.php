<?php

namespace App\Models;

use App\Models\Draw;
use App\Models\User;
use App\Models\GameType;
use App\Models\Location;
use App\Models\BetRatioAudit;
use App\Observers\BetRatioObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
 
#[ObservedBy([BetRatioObserver::class])]
class BetRatio extends Model
{
    use HasFactory;

    //fillable
    protected $fillable = [
        'draw_id',
        'game_type_id',
        'bet_number',
        'sub_selection',
        'max_amount',
        'user_id',
        'location_id',
    ];
   

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function draw(){
        return $this->belongsTo(Draw::class);
    }

    public function gameType(){
        return $this->belongsTo(GameType::class);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function betRatioAudit(){
        return $this->hasMany(BetRatioAudit::class);
    }

    
}   
