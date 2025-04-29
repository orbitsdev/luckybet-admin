<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\User;
use App\Models\Result;
use App\Models\Commission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Claim extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'bet_id',
        'teller_id',
        'amount',
        'commission_amount',  // optional
        'claimed_at',
        'qr_code_data',       // optional
    ];


    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function commission()
    {
        return $this->hasOne(Commission::class, 'claim_id');
    }
}
