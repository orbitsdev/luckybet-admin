<?php

namespace App\Models;

use App\Models\BetRatio;
use Illuminate\Database\Eloquent\Model;

class BetRatioAudit extends Model
{
    
    protected $fillable = [
        'bet_ratio_id',
        'user_id',
        'old_max_amount',
        'new_max_amount',
        'action',
    ];

    public function betRatio(){
        return $this->belongsTo(BetRatio::class);
    }
}
