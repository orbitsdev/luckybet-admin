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
        'draw_id',
        'game_type_id',    // Foreign key to game_types table
        'teller_id',
        'customer_id',    // optional
        'location_id',
        'bet_date',
        'ticket_id',
        'is_claimed',     // boolean, default false
        'is_rejected',    // boolean, default false
        'is_combination', // true/false
        'd4_sub_selection', // enum: 's2' or 's3' for D4 game type
    ];

    protected $casts = [
        'bet_date' => 'date',
        'claimed_at' => 'datetime',
        'is_combination' => 'boolean',
        'is_claimed' => 'boolean',
        'is_rejected' => 'boolean',
    ];

    /**
     * Append additional attributes to the model.
     *
     * @var array
     */
    protected $appends = ['is_winner'];


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

    public function claim()
    {
        return $this->hasOne(Claim::class);
    }

    public function commission()
    {
        return $this->hasOne(Commission::class, 'bet_id');
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
        if (!$ignoreClaimStatus && !$this->is_claimed) {
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

                if (!$isWinner && $this->d4_sub_selection) {
                    if ($this->d4_sub_selection === 's2' && $result->s2_winning_number) {

                        $isWinner = substr($this->bet_number, -2) === $result->s2_winning_number;
                    } else if ($this->d4_sub_selection === 's3' && $result->s3_winning_number) {

                        $isWinner = substr($this->bet_number, -3) === $result->s3_winning_number;
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
}
