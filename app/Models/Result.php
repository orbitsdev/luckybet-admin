<?php

namespace App\Models;

use App\Models\Draw;
use App\Models\User;
use App\Models\GameType;
use App\Observers\ResultObserver;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

// #[ObservedBy([ResultObserver::class])]
class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_id',             // Reference to the draw
        'draw_date',           // Copy of draw date for reporting
        'draw_time',           // Copy of draw time for reporting
        's2_winning_number',   // S2 winning number (e.g., 21)
        's3_winning_number',   // S3 winning number (e.g., 456)
        'd4_winning_number',   // D4 winning number (e.g., 7890)
        'coordinator_id',      // User who inputted the result
    ];

    protected $casts = [
        'draw_date' => 'date',
        'draw_time' => 'string',
    ];

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }

    /**
     * Get the claims associated with this result
     */
    public function claims()
    {
        return $this->hasMany(Claim::class);
    }
}
