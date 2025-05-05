<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Draw extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_date', 'draw_time', 'schedule_id', 'game_type_id',
        'is_open',
    ];
    
    protected $casts = [
        'draw_date' => 'date',
        'is_open' => 'boolean',
    ];

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
    
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
    
    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }
    
    public function result()
    {
        return $this->hasOne(Result::class)->where('draw_date', $this->draw_date)
                                         ->where('draw_time', $this->draw_time);
    }
}
