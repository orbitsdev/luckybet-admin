<?php

namespace App\Http\Controllers\Api;

use App\Models\NumberFlag;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NumberFlagResource;

class NumberFlagController extends Controller
{
    /**
     * Display a listing of number flags.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $location_id = $user->location_id;
        
        $query = NumberFlag::with(['schedule', 'location'])
            ->where('location_id', $location_id)
            ->when($request->filled('date'), function($q) use ($request) {
                return $q->whereDate('date', $request->date);
            })
            ->when($request->filled('type'), function($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->when($request->filled('schedule_id'), function($q) use ($request) {
                return $q->where('schedule_id', $request->schedule_id);
            })
            ->when($request->filled('is_active'), function($q) use ($request) {
                return $q->where('is_active', $request->is_active == 'true');
            })
            ->when($request->filled('search'), function($q) use ($request) {
                return $q->where('number', 'like', '%' . $request->search . '%');
            });
            
        $numberFlags = $query->latest()->paginate($request->get('per_page', 20));
        
        return ApiResponse::paginated($numberFlags, 'Number flags retrieved successfully', NumberFlagResource::class);
    }

    /**
     * Store a newly created number flag.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required|string|max:10',
            'schedule_id' => 'required|exists:schedules,id',
            'date' => 'required|date',
            'type' => 'required|in:sold_out,low_win',
        ]);
        
        $user = $request->user();
        $data['location_id'] = $user->location_id;
        $data['is_active'] = true;
        
        // Check if the number flag already exists for this date, schedule, and location
        $existingFlag = NumberFlag::where('number', $data['number'])
            ->where('schedule_id', $data['schedule_id'])
            ->where('date', $data['date'])
            ->where('location_id', $data['location_id'])
            ->where('type', $data['type'])
            ->first();
            
        if ($existingFlag) {
            // If it exists but is inactive, reactivate it
            if (!$existingFlag->is_active) {
                $existingFlag->is_active = true;
                $existingFlag->save();
                return ApiResponse::success(new NumberFlagResource($existingFlag), 'Number flag reactivated successfully');
            }
            
            return ApiResponse::error('This number is already flagged for this schedule, date, and location', 422);
        }
        
        $numberFlag = NumberFlag::create($data);
        $numberFlag->load(['schedule', 'location']);
        
        return ApiResponse::success(new NumberFlagResource($numberFlag), 'Number flag created successfully', 201);
    }

    /**
     * Display the specified number flag.
     *
     * @param NumberFlag $numberFlag
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(NumberFlag $numberFlag)
    {
        $user = Auth::user();
        
        // Ensure the number flag belongs to the user's location
        if ($numberFlag->location_id !== $user->location_id) {
            return ApiResponse::error('You do not have permission to view this number flag', 403);
        }
        
        $numberFlag->load(['schedule', 'location']);
        
        return ApiResponse::success(new NumberFlagResource($numberFlag), 'Number flag retrieved successfully');
    }

    /**
     * Update the specified number flag.
     *
     * @param Request $request
     * @param NumberFlag $numberFlag
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, NumberFlag $numberFlag)
    {
        $user = Auth::user();
        
        // Ensure the number flag belongs to the user's location
        if ($numberFlag->location_id !== $user->location_id) {
            return ApiResponse::error('You do not have permission to update this number flag', 403);
        }
        
        $data = $request->validate([
            'type' => 'sometimes|in:sold_out,low_win',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $numberFlag->update($data);
        $numberFlag->load(['schedule', 'location']);
        
        return ApiResponse::success(new NumberFlagResource($numberFlag), 'Number flag updated successfully');
    }

    /**
     * Remove the specified number flag (soft delete by setting is_active to false).
     *
     * @param NumberFlag $numberFlag
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(NumberFlag $numberFlag)
    {
        $user = Auth::user();
        
        // Ensure the number flag belongs to the user's location
        if ($numberFlag->location_id !== $user->location_id) {
            return ApiResponse::error('You do not have permission to delete this number flag', 403);
        }
        
        // Soft delete by setting is_active to false
        $numberFlag->is_active = false;
        $numberFlag->save();
        
        return ApiResponse::success(null, 'Number flag deactivated successfully');
    }
}
