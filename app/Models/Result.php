<?php

namespace App\Models;

use App\Models\User;
use App\Models\Draw;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_id',             // Reference to the draw
        'game_type_id',        // Reference to the game type
        'draw_date',           // Date of the draw
        'draw_time',           // Time of the draw (e.g., 2pm)
        's2_winning_number',   // S2 winning number (e.g., 21)
        's3_winning_number',   // S3 winning number (e.g., 456)
        'd4_winning_number',   // D4 winning number (e.g., 7890)
        'coordinator_id',      // User who inputted
    ];
    
    protected $casts = [
        'draw_date' => 'date',
    ];

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
    
    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }
    
    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }
}
