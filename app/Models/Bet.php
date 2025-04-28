<?php

namespace App\Models;

use App\Models\User;
use App\Models\Claim;
use App\Models\Location;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bet_number', 'amount', 'schedule_id', 'teller_id', 'customer_id', 'location_id',
        'bet_date', 'ticket_id', 'status', 'is_combination'
    ];

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function claim()
    {
        return $this->hasOne(Claim::class);
    }
    //
}
