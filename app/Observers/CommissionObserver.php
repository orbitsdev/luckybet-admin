<?php

namespace App\Observers;

use App\Models\Commission;
use App\Models\CommissionHistory;
use Illuminate\Support\Facades\Auth;

class CommissionObserver
{
   
    /**
     * Handle the Commission "updated" event.
     */
    public function updated(Commission $commission): void
    {
        // Only log if the rate was changed
    if ($commission->isDirty('rate')) {
        CommissionHistory::create([
            'commission_id' => $commission->id,
            'old_rate'      => $commission->getOriginal('rate'),
            'new_rate'      => $commission->rate,
            'changed_by'    => Auth::user()->id, // Or pass the admin user in some other way
            // 'changed_at' will auto-fill with current timestamp
        ]);
    }
    }

    
}
