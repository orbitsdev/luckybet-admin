<?php

namespace App\Observers;

use App\Models\Bet;

class BetObserver
{
    /**
     * Handle the Bet "creating" event.
     * This will auto-generate a ticket_id if one isn't provided.
     */
    public function creating(Bet $bet): void
    {
        if (empty($bet->ticket_id)) {
            // Generate format: BET-YYYYMMDD-XXXXX (where X is random)
            $date = now()->format('Ymd');
            $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $bet->ticket_id = "BET-{$date}-{$random}";
        }
    }
}
