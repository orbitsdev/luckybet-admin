<?php

namespace App\Models;

use App\Models\Draw;
use App\Models\User;
use App\Models\GameType;
use App\Models\BetRatioAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Observers\BetRatioObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
 
#[ObservedBy([BetRatioObserver::class])]
class BetRatio extends Model
{
    use HasFactory;

    //filalble
    protected $fillable = [
        'bet_ratio',
        'user_id',
        'draw_id',
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

    public function betRatioAudit(){
        return $this->hasMany(BetRatioAudit::class);
    }

    
}   
