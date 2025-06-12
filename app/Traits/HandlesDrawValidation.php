<?php

namespace App\Traits;

use App\Models\Draw;
use Carbon\Carbon;

trait HandlesDrawValidation
{
    protected function getValidDraws($date)
    {
        
        $draws = Draw::with('result')
                ->whereDate('draw_date', $date)
                ->orderBy('draw_time')
                ->get();

            // Filter for draws with complete results
            $validDraws = $draws->filter(function ($draw) {
                if (!$draw->result) return false;
                return $draw->result->s2_winning_number &&
                    $draw->result->s3_winning_number &&
                    $draw->result->d4_winning_number;
            });

            // Get missing results info
            $missingResults = [];
            foreach ($draws as $draw) {
                $missing = [];

                if (!$draw->result) {
                    $missing = ['S2', 'S3', 'D4'];
                } else {
                    if (!$draw->result->s2_winning_number) $missing[] = 'S2';
                    if (!$draw->result->s3_winning_number) $missing[] = 'S3';
                    if (!$draw->result->d4_winning_number) $missing[] = 'D4';
                }

                if (!empty($missing)) {
                    $missingResults[] = [
                        'time' => Carbon::parse($draw->draw_time)->format('g:i A'),
                        'missing' => $missing,
                    ];
                }
            }

            return [
                'validDraws' => $validDraws,
                'missingResults' => $missingResults,
                'hasPendingDraws' => count($missingResults) > 0
            ];
        }
    }
