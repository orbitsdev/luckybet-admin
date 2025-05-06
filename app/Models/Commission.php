<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\User;
use App\Models\Claim;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'teller_id',       // References users.id
        'rate',            // % rate
        'amount',          // Computed commission
        'commission_date', // Date of commission
        'type',            // 'bet' or 'claim'
        'bet_id',          // For type 'bet'
        'claim_id'         // For type 'claim'
    ];
    
    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'commission_date' => 'date',
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }
}
