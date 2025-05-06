<?php

namespace App\Observers;

use App\Models\Result;

class ResultObserver
{
    /**
     * Handle the Result "created" event.
     */
    public function created(Result $result): void
    {
        // Copy draw_date and draw_time from the related Draw model
        if ($result->draw) {
            $result->draw_time = $result->draw->draw_time;
            $result->draw_date = $result->draw->draw_date;
            $result->save();
        }
    }

    /**
     * Handle the Result "updated" event.
     */
    public function updated(Result $result): void
    {
        //
    }

    /**
     * Handle the Result "deleted" event.
     */
    public function deleted(Result $result): void
    {
        //
    }

    /**
     * Handle the Result "restored" event.
     */
    public function restored(Result $result): void
    {
        //
    }

    /**
     * Handle the Result "force deleted" event.
     */
    public function forceDeleted(Result $result): void
    {
        //
    }
}
