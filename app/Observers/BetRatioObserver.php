<?php

namespace App\Observers;

use App\Models\BetRatio;
use App\Models\BetRatioAudit;
use Illuminate\Support\Facades\Auth;

class BetRatioObserver
{
    /**
     * Handle the BetRatio "created" event.
     */
    public function created(BetRatio $betRatio): void
    {
        //
    }

    /**
     * Handle the BetRatio "updated" event.
     */
    public function updated(BetRatio $betRatio): void
    {
    $original = $betRatio->getOriginal('max_amount');
    $new = $betRatio->max_amount;
    $userId =Auth::user()->id() ?? $betRatio->user_id; // fallback

    BetRatioAudit::create([
        'bet_ratio_id' => $betRatio->id,
        'user_id' => $userId,
        'old_max_amount' => $original,
        'new_max_amount' => $new,
        'action' => 'update',
    ]);
    }

    /**
     * Handle the BetRatio "deleted" event.
     */
    public function deleted(BetRatio $betRatio): void
    {
        //
    }

    /**
     * Handle the BetRatio "restored" event.
     */
    public function restored(BetRatio $betRatio): void
    {
        //
    }

    /**
     * Handle the BetRatio "force deleted" event.
     */
    public function forceDeleted(BetRatio $betRatio): void
    {
        //
    }
}
