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
        $gameTypes = GameType::where('is_active', true)->get();
        return ApiResponse::success(GameTypeResource::collection($gameTypes));
    }

    public function schedules()
    {
        $schedules = Schedule::where('is_active', true)->get();
        return ApiResponse::success(ScheduleResource::collection($schedules));
    }

    public function draws()
    {
        $draws = Draw::where('is_active', true)
            ->where('is_open', true)
            ->orderBy('draw_date')
            ->orderBy('draw_time')
            ->get();
        return ApiResponse::success(DrawResource::collection($draws));
    }
}
