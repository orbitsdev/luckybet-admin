<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoldOutNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'coordinator_id',
        'draw_date',
        'draw_time',
        'game_type_id',
        'bet_number',
        'reason',
    ];

    protected $casts = [
        'draw_date' => 'date',
        'draw_time' => 'string',
    ];

    /**
     * Get the coordinator that created this sold out number
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Get the game type that this sold out number applies to
     */
    public function gameType(): BelongsTo
    {
        return $this->belongsTo(GameType::class);
    }
}
