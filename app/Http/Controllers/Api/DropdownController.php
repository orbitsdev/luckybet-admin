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
        $today = now()->format('l'); // 'Sunday', 'Monday', etc.

        $gameTypesQuery = GameType::where('is_active', true);
        // Exclude D4 on Sundays
        if ($today === 'Sunday') {
            $gameTypesQuery->where('code', '!=', 'D4');
        }
        $gameTypes = cache()->remember('dropdown_game_types_' . $today, 3600, function() use ($gameTypesQuery) {
            return GameTypeResource::collection($gameTypesQuery->get());
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
        // Get distinct dates first
        $draws = Draw::orderBy('draw_date', 'desc')
        ->orderBy('draw_time', 'asc')
        ->get();

    return ApiResponse::success([
        'available_draws' => DrawResource::collection($draws),
    ], 'Available draw list fetched successfully');


    }
}
