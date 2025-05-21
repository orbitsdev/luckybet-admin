<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\GameType;

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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
        return $this->belongsTo(\App\Models\Draw::class);
    }

    /**
     * Get the user who set this low win number
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
