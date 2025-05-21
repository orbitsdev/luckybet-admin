<?php

namespace App\Models;

use App\Models\BetRatio;
use Illuminate\Database\Eloquent\Model;

class BetRatioAudit extends Model
{
    
    protected $fillable = [
        'bet_ratio_id',
        'user_id',
        'draw_id',
        'bet_ratio',
    ];

    public function betRatio(){
        return $this->belongsTo(BetRatio::class);
    }
}
