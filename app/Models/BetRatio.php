<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BetRatio extends Model
{
    use HasFactory;

    protected $fillable = [
        'coordinator_id',
        'draw_date',
        's2_limit',
        's3_limit',
        'd4_limit',
        's2_win_amount',
        's3_win_amount',
        'd4_win_amount',
        's2_low_win_amount',
        's3_low_win_amount',
        'd4_low_win_amount',
    ];

    protected $casts = [
        'draw_date' => 'date',
        's2_limit' => 'decimal:2',
        's3_limit' => 'decimal:2',
        'd4_limit' => 'decimal:2',
        's2_win_amount' => 'decimal:2',
        's3_win_amount' => 'decimal:2',
        'd4_win_amount' => 'decimal:2',
        's2_low_win_amount' => 'decimal:2',
        's3_low_win_amount' => 'decimal:2',
        'd4_low_win_amount' => 'decimal:2',
    ];

    /**
     * Get the coordinator that owns the bet ratio
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Get the restrictions for this bet ratio
     */
    public function restrictions(): HasMany
    {
        return $this->hasMany(BetRatioRestriction::class);
    }
}
