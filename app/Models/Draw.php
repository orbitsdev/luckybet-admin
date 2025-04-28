<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Draw extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_date', 'draw_time', 'type', 'winning_number', 'is_open',
    ];

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }
}
