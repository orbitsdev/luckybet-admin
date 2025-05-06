<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BetRatioRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'bet_ratio_id',
        'game_type_id',
        'number',
        'amount_limit',
        'draw_time',
    ];

    protected $casts = [
        'amount_limit' => 'decimal:2',
        'draw_time' => 'string',
    ];

    /**
     * Get the bet ratio that owns the restriction
     */
    public function betRatio(): BelongsTo
    {
        return $this->belongsTo(BetRatio::class);
    }

    /**
     * Get the game type that this restriction applies to
     */
    public function gameType(): BelongsTo
    {
        return $this->belongsTo(GameType::class);
    }
}
