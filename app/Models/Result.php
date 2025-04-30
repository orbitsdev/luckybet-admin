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
        'draw_id',         // Reference to the draw
        'draw_date',       // Date of the draw
        'draw_time',       // Time of the draw (e.g., 2pm)
        'type',            // Type of draw (S2, S3, D4)
        'winning_number',  // Example: 21, 456, 7890
        'coordinator_id',  // User who inputted
    ];

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }
    
    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }
}
