<?php

namespace App\Http\Controllers\Api;

use App\Models\GameType;
use App\Models\Schedule;
use App\Models\Draw;
use App\Helpers\ApiResponse;
use App\Http\Resources\GameTypeResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\DrawResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DropdownController extends Controller
{
    public function gameTypes()
    {
        $gameTypes = cache()->remember('dropdown_game_types', 3600, function() {
            return GameTypeResource::collection(GameType::where('is_active', true)->get());
        });
        return ApiResponse::success($gameTypes);
    }

    public function schedules()
    {
        $schedules = cache()->remember('dropdown_schedules', 3600, function() {
            return ScheduleResource::collection(Schedule::where('is_active', true)->get());
        });
        return ApiResponse::success($schedules);
    }

    public function draws()
    {
        $draws = Draw::where('is_open', true)
            ->orderBy('draw_date')
            ->orderBy('draw_time')
            ->get();
        return ApiResponse::success(DrawResource::collection($draws));
    }

    /**
     * Get all available draw dates for dropdown/calendar
     */
    public function availableDates()
    {
        $dates = Draw::select('draw_date')
            ->distinct()
            ->orderBy('draw_date', 'desc')
            ->pluck('draw_date');

        $datesWithFormatted = $dates->map(function ($date) {
            return [
                'date' => $date,
                'date_formatted' => \Carbon\Carbon::parse($date)->format('F j, Y'),
            ];
        });

        return ApiResponse::success([
            'available_dates' => $datesWithFormatted,
        ], 'Available draw dates fetched successfully');
    }
}
