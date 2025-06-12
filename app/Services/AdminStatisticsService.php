<?php
namespace App\Services;

use App\Models\Bet;
use App\Models\User;

class AdminStatisticsService
{

    /**
     * Generate sales summary grouped by teller and game type.
     *
     * @param array $tellerIds
     * @param array $drawIds
     * @return array
     */
    public function summarizeByTellers(array $tellerIds, array $drawIds): array
    {
        $bets = Bet::placed()
            ->whereIn('teller_id', $tellerIds)
            ->whereIn('draw_id', $drawIds)
            ->where('is_rejected', false)
            ->with(['draw.result', 'gameType', 'teller', 'receipt'])
            ->get();

        $summary = [];

        foreach ($tellerIds as $id) {
            $summary[$id] = [
                'id' => $id,
                'name' => User::find($id)?->name ?? 'Unknown',
                'total_sales' => 0,
                'total_hits' => 0,
                'total_gross' => 0,
                'game_types' => [],
            ];
        }

        foreach ($bets as $bet) {
            $tellerId = $bet->teller_id;
            $gameTypeCode = $bet->gameType->code ?? 'Unknown';
            $gameTypeName = $bet->gameType->name ?? 'Unknown';
            $displayGameType = $gameTypeCode;

            if ($gameTypeCode === 'D4' && $bet->d4_sub_selection) {
                $displayGameType = "D4-{$bet->d4_sub_selection}";
            }

            if (!isset($summary[$tellerId]['game_types'][$displayGameType])) {
                $summary[$tellerId]['game_types'][$displayGameType] = [
                    'name' => $gameTypeName . ($bet->d4_sub_selection ? " ({$bet->d4_sub_selection})" : ""),
                    'code' => $displayGameType,
                    'total_sales' => 0,
                    'total_hits' => 0,
                    'total_gross' => 0,
                ];
            }

            // Add to sales
            $summary[$tellerId]['total_sales'] += $bet->amount;
            $summary[$tellerId]['game_types'][$displayGameType]['total_sales'] += $bet->amount;

            // Add to hits if result exists
            if ($bet->draw && $bet->draw->result && $bet->winning_amount > 0) {
                $summary[$tellerId]['total_hits'] += $bet->winning_amount;
                $summary[$tellerId]['game_types'][$displayGameType]['total_hits'] += $bet->winning_amount;
            }
        }

        // Final gross calculation
        foreach ($summary as $tellerId => $data) {
            foreach ($data['game_types'] as $code => $gt) {
                $summary[$tellerId]['game_types'][$code]['total_gross'] =
                    $gt['total_sales'] - $gt['total_hits'];
            }

            $summary[$tellerId]['total_gross'] =
                $data['total_sales'] - $data['total_hits'];
        }

        return array_values(array_filter($summary, fn ($t) => $t['total_sales'] > 0));
    }
}

