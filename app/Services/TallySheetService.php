<?php

namespace App\Services;

use App\Models\TallySheet;
use Carbon\Carbon;

class TallySheetService
{
    public static function updateSheet(int $tellerId, int $locationId, float $salesIncrement = 0, float $claimsIncrement = 0, float $commissionIncrement = 0): TallySheet
    {
        $today = Carbon::now()->toDateString();

        $tallySheet = TallySheet::firstOrCreate(
            [
                'teller_id' => $tellerId,
                'location_id' => $locationId,
                'sheet_date' => $today,
            ],
            [
                'total_sales' => 0,
                'total_claims' => 0,
                'total_commission' => 0,
                'net_amount' => 0,
            ]
        );

        $tallySheet->total_sales += $salesIncrement;
        $tallySheet->total_claims += $claimsIncrement;
        $tallySheet->total_commission += $commissionIncrement;
        $tallySheet->net_amount = ($tallySheet->total_sales - $tallySheet->total_claims) + $tallySheet->total_commission;

        $tallySheet->save();

        return $tallySheet;
    }
}
