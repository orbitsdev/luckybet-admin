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

    protected $fillable = ['teller_id', 'rate', 'amount', 'commission_date', 'type', 'bet_id', 'claim_id'];

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
