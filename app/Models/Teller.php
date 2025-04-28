<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\User;
use App\Models\Claim;
use App\Models\Commission;
use App\Models\TallySheet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teller extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'coordinator_id', 'commission_rate', 'balance'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'teller_id');
    }

    public function claims()
    {
        return $this->hasMany(Claim::class, 'teller_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class, 'teller_id');
    }

    public function tallySheets()
    {
        return $this->hasMany(TallySheet::class);
    }
}
