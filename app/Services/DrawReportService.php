<?php

namespace App\Services;

use App\Models\Draw;
use App\Models\Bet;
use App\Models\Claim;
use Illuminate\Support\Collection;

class DrawReportService
{
    public function getSummaryByDate(string $date): Collection
    {
        $draws = Draw::with('result')->whereDate('draw_date', $date)->get();

        return $draws->map(function ($draw) {
            $totalSales = Bet::where('draw_id', $draw->id)->sum('amount');
            $betIds = Bet::where('draw_id', $draw->id)->pluck('id');
            $totalHits = Claim::whereIn('bet_id', $betIds)->sum('amount');

            $result = $draw->result;

            return [
                'draw_id' => $draw->id,
                'draw_date' => $draw->draw_date->format('Y-m-d'),
                'draw_time' => $draw->draw_time,
                'total_sales' => $totalSales,
                'total_hits' => $totalHits,
                'gross' => $totalSales - $totalHits,
                'has_result' => !is_null($result),
                's2_result' => $result->s2_winning_number ?? null,
                's3_result' => $result->s3_winning_number ?? null,
                'd4_result' => $result->d4_winning_number ?? null,
            ];
        });
    }

    public function getSummaryForAll(): Collection
    {
        $draws = Draw::with('result')->get();

        return $draws->map(function ($draw) {
            $bets = Bet::where('draw_id', $draw->id)->get()->groupBy('teller_id');
            $tellerSummaries = [];

            foreach ($bets as $tellerId => $tellerBets) {
                $totalSales = $tellerBets->sum('amount');
                $betIds = $tellerBets->pluck('id');
                $totalHits = Claim::whereIn('bet_id', $betIds)->sum('amount');
                $tellerSummaries[] = [
                    'teller_id' => $tellerId,
                    'teller_name' => optional($tellerBets->first()->teller)->name ?? 'Unknown',
                    'total_sales' => $totalSales,
                    'total_hits' => $totalHits,
                    'gross' => $totalSales - $totalHits,
                ];
            }

            $result = $draw->result;

            return [
                'draw_id' => $draw->id,
                'draw_date' => $draw->draw_date->format('Y-m-d'),
                'draw_time' => $draw->draw_time,
                's2_result' => $result->s2_winning_number ?? null,
                's3_result' => $result->s3_winning_number ?? null,
                'd4_result' => $result->d4_winning_number ?? null,
                'tellers' => $tellerSummaries,
            ];
        });
    }

    // Existing methods remain unchanged
}