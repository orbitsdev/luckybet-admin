<?php

namespace App\Models;

use App\Models\User;
use App\Models\GameType;
use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LowWinNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'draw_id',
        'game_type_id',
        'bet_number',
        'winning_amount',
        'reason',
        'user_id',
        'location_id',
        'is_active',
        'start_date',
        'end_date',
        'd4_sub_selection',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'winning_amount' => 'decimal:2',
    ];

    /**
     * Get the game type that this low win number applies to
     */
    public function gameType(): BelongsTo
    {
        return $this->belongsTo(GameType::class);
    }

    /**
     * Get the draw that this low win number applies to
     */
    public function draw(): BelongsTo
    {
        return $this->belongsTo(Draw::class);
    }

    /**
     * Get the user who set this low win number
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     public function location(){
        return $this->belongsTo(Location::class);
    }
}
