<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'sometimes|in:admin,coordinator,teller,customer',
            'phone' => 'nullable|string|max:20',
            'location_id' => 'nullable|exists:locations,id',
            'is_active' => 'sometimes|boolean',
            'coordinator_id' => 'nullable|exists:users,id',
            'profile_photo_path' => 'nullable|string|max:2048',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'teller',
            'phone' => $data['phone'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'coordinator_id' => $data['coordinator_id'] ?? null,
            'profile_photo_path' => $data['profile_photo_path'] ?? null,
        ]);

        $user->load('location');
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'User registered successfully', 201);
    }

    public function login(Request $request)
    {
        // Validate request based on whether email or username is provided
        if ($request->has('email')) {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $data['email'])->first();
        } else {
            $data = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('username', $data['username'])->first();
        }

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return ApiResponse::error('Invalid credentials', 422);
        }

        $user->load('location');
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    } 

    public function user(Request $request)
    {
        return ApiResponse::success(new UserResource($request->user()->load('location')));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return ApiResponse::success(null, 'User logged out successfully');
    }
}
