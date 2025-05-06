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
        'result_id',
        'teller_id',
        'amount',
        'commission_amount',  // Commission from the claim
        'status',            // pending, processed, rejected
        'claim_at',          // When claim was processed
        'qr_code_data',      // Embedded QR ticket data
    ];
    
    protected $casts = [
        'claim_at' => 'datetime',
    ];

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }
    
    public function commission()
    {
        return $this->hasOne(Commission::class, 'claim_id');
    }
}
