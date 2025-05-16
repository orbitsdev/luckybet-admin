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
        'game_type_id',
        'amount',
        'bet_number',
        'reason',
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
}
