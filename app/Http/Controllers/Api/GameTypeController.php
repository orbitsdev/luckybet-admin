<?php

namespace App\Http\Controllers\Api;

use App\Models\GameType;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameTypeResource;

class GameTypeController extends Controller
{
    /**
     * Get a list of all active game types
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $gameTypes = GameType::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return ApiResponse::success(
            GameTypeResource::collection($gameTypes), 
            'Game types loaded'
        );
    }
}
