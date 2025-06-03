<?php

namespace App\Models;

use App\Models\Draw;
use App\Models\User;
use App\Models\Claim;
use App\Models\Location;
use App\Models\Schedule;
use App\Models\Commission;
use App\Observers\BetObserver;
use Illuminate\Database\Eloquent\Model;
// SoftDeletes trait removed as per new structure
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([BetObserver::class])]
class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'bet_number',
        'amount',
        'winning_amount',
        'draw_id',
        'game_type_id',    
        'teller_id',
        'customer_id',   
        'location_id',
        'bet_date',
        'ticket_id',
        'is_claimed',  
        'is_rejected',    
        'is_combination',
        'd4_sub_selection',
        'commission_rate',
        'commission_amount',
        'receipt_id'
    ];

    protected $casts = [
        'bet_date' => 'date',
        'claimed_at' => 'datetime',
        'is_combination' => 'boolean',
        'is_claimed' => 'boolean',
        'is_rejected' => 'boolean',
        'winning_amount' => 'decimal:2',
    ];

    /**
     * Append additional attributes to the model.
     *
     * @var array
     */
    protected $appends = ['is_winner', 'winning_amount', 'is_low_win'];


    public function draw()
    {
        return $this->belongsTo(Draw::class);
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }

    public function teller()
    {
        return $this->belongsTo(User::class, 'teller_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }


    public function commission()
    {
        return $this->hasOne(Commission::class, 'bet_id');
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    /**
     * Determine if the bet is a winner by comparing with results.
     * 
     * @param bool $ignoreClaimStatus If true, will check if bet is a winner regardless of claim status
     * @return bool
     */
    public function getIsWinnerAttribute($ignoreClaimStatus = false)
    {
        // Only claimed bets can be winners, unless we explicitly ignore claim status
        // Safely check if the is_claimed attribute exists and is true
        if (!$ignoreClaimStatus && !(isset($this->attributes['is_claimed']) && $this->attributes['is_claimed'])) {
            return false;
        }

        // Get the result from the eager loaded relationship if available
        // Otherwise, load it manually
        $result = $this->draw->result ?? \App\Models\Result::where('draw_id', $this->draw_id)->first();
        if (!$result) {
            return false;
        }

        // Get the game type
        $gameType = $this->gameType;
        if (!$gameType) {
            return false;
        }

        // Check if bet is a winner based on game type
        switch ($gameType->code) {
            case 'S2':
                return $this->bet_number === $result->s2_winning_number;

            case 'S3':
                return $this->bet_number === $result->s3_winning_number;

            case 'D4':

                $isWinner = $this->bet_number === $result->d4_winning_number;

            // D4 sub-selection logic: compare to last 2/3 digits of D4 result, not S2/S3 result fields
            if (!$isWinner && $this->d4_sub_selection && $result->d4_winning_number) {
                $sub = strtoupper($this->d4_sub_selection);
                if ($sub === 'S2') {
                    // Compare last 2 digits of D4 result to bet number (pad bet number to 2 digits)
                    $isWinner = substr($result->d4_winning_number, -2) === str_pad($this->bet_number, 2, '0', STR_PAD_LEFT);
                } else if ($sub === 'S3') {
                    // Compare last 3 digits of D4 result to bet number (pad bet number to 3 digits)
                    $isWinner = substr($result->d4_winning_number, -3) === str_pad($this->bet_number, 3, '0', STR_PAD_LEFT);
                }
            }

            return $isWinner;

            default:
                return false;
        }
    }
    
    /**
     * Check if a bet is a hit (winner) regardless of claim status
     * This is used by the hit list to find all winning bets
     * 
     * @return bool
     */
    public function isHit()
    {
        return $this->getIsWinnerAttribute(true);
    }

    /**
     * Get the winning amount for this bet, considering low win rules.
     * Returns null if not set.
     */
    public function getWinningAmountAttribute()
    {
        // If the value is set in the database, always use it (new bets)
        if (!is_null($this->attributes['winning_amount'] ?? null)) {
            return $this->attributes['winning_amount'];
        }
        
        // Safely get required attributes, return null if any are missing
        $drawId = $this->attributes['draw_id'] ?? null;
        $gameTypeId = $this->attributes['game_type_id'] ?? null;
        $locationId = $this->attributes['location_id'] ?? null;
        $betNumber = $this->attributes['bet_number'] ?? null;
        
        // If any required attributes are missing, return null
        if ($drawId === null || $gameTypeId === null || $locationId === null || $betNumber === null) {
            return null;
        }
        
        // Otherwise, match controller logic: draw, game type, location, bet number
        $lowWin = \App\Models\LowWinNumber::where('draw_id', $drawId)
            ->where('game_type_id', $gameTypeId)
            ->where('location_id', $locationId)
            ->where('bet_number', $betNumber)
            ->first();

        // Fallback: try global low win (bet_number is null or empty)
        if (!$lowWin) {
            $lowWin = \App\Models\LowWinNumber::where('draw_id', $drawId)
                ->where('game_type_id', $gameTypeId)
                ->where('location_id', $locationId)
                ->where(function ($query) {
                    $query->whereNull('bet_number')->orWhere('bet_number', '');
                })
                ->first();
        }

        if ($lowWin && isset($lowWin->winning_amount)) {
            return $lowWin->winning_amount;
        }

        // Get amount safely
        $amount = $this->attributes['amount'] ?? null;
        if ($amount === null) {
            return null;
        }

        // Default payout
        return \App\Models\WinningAmount::where('game_type_id', $gameTypeId)
            ->where('location_id', $locationId)
            ->where('amount', $amount)
            ->value('winning_amount');
    }

    /**
     * Returns true if this bet is under a low win rule.
     */
    public function getIsLowWinAttribute()
    {
        // Safely get required attributes, return false if any are missing
        $gameTypeId = $this->attributes['game_type_id'] ?? null;
        $betNumber = $this->attributes['bet_number'] ?? null;
        
        // If any required attributes are missing, return false
        if ($gameTypeId === null || $betNumber === null) {
            return false;
        }
        
        $lowWin = \App\Models\LowWinNumber::where('game_type_id', $gameTypeId)
                        ->where(function($q) use ($betNumber) {
                $q->whereNull('bet_number')
                  ->orWhere('bet_number', $betNumber);
            })
            ->first();
        return $lowWin !== null;
    }
    
    /**
     * Scope a query to only include bets with receipts in 'placed' status.
     * This ensures that only finalized bets are included in reports and calculations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlaced($query)
    {
        return $query->whereHas('receipt', function($q) {
            $q->where('status', 'placed');
        })->orWhereNull('receipt_id'); // Include legacy bets without receipt_id
    }
}
