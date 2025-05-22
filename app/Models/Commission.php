<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\User;
use App\Models\Claim;
use App\Models\CommissionHistory;
use App\Observers\CommissionObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([CommissionObserver::class])]
class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'teller_id',
        'rate',
        'bet_id',
    ];
    
    protected $casts = [
        'rate' => 'decimal:2',
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }


    public function commissionHistory()
    {
        return $this->hasMany(CommissionHistory::class);
    }
}
