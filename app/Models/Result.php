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
        'draw_id',
        'draw_date',
        'draw_time',
        's2_winning_number',
        's3_winning_number',
        'd4_winning_number',
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
   
}
