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
        // Use DrawResource for consistency with other endpoints
        // This requires selecting all necessary fields
        $dates = Draw::orderBy('draw_date', 'desc')
            ->groupBy('draw_date')
            ->get(['id', 'draw_date', 'draw_time', 'is_open', 'is_active']);
            
        return ApiResponse::success([
            'available_dates' => DrawResource::collection($dates),
        ], 'Available draw dates fetched successfully');
    }
}
