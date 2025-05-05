<?php

namespace App\Models;

use App\Models\Draw;
use App\Models\User;
use App\Models\Claim;
use App\Models\Location;
use App\Models\Schedule;
use App\Models\Commission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bet_number',
        'amount',
        'draw_id',
        'game_type_id',    // Foreign key to game_types table
        'teller_id',
        'customer_id',    // optional
        'location_id',
        'bet_date',
        'ticket_id',
        'status',         // active, cancelled, won, lost, claimed
        'is_combination', // true/false
    ];
    
    protected $casts = [
        'bet_date' => 'date',
        'is_combination' => 'boolean',
    ];


    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }
    
    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }

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

    public function claim()
    {
        return $this->hasOne(Claim::class);
    }

    public function commission()
    {
        return $this->hasOne(Commission::class, 'bet_id');
    }
}
