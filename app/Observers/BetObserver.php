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
            // Generate a random 6-character uppercase alphanumeric ticket ID
            $bet->ticket_id = strtoupper(\Illuminate\Support\Str::random(6));
        }
    }
}
