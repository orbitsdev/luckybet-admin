<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\User;
use App\Models\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'bet_id', 'result_id', 'teller_id', 'amount', 'commission_amount', 'claimed_at', 'qr_code_data'
    ];

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }
}
