<?php

namespace App\Models;

use App\Models\Bet;
use App\Models\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'draw_time',
        'is_active',
        'is_open', // <- ADD THIS
    ];


    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }
}
