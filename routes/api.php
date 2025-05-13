<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\BettingController;
use App\Http\Controllers\Api\TallySheetController;
use App\Http\Controllers\Api\NumberFlagController;
use App\Http\Controllers\Api\GameTypeController;
use App\Http\Controllers\Api\CoordinatorReportController;
use App\Http\Controllers\Api\DropdownController;
use App\Http\Controllers\Api\TellerReportController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // User Authentication
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dropdown Data
    Route::get('/dropdown/game-types', [DropdownController::class, 'gameTypes']);
    Route::get('/dropdown/schedules', [DropdownController::class, 'schedules']);
    Route::get('/dropdown/draws', [DropdownController::class, 'draws']);
    Route::get('/dropdown/available-dates', [DropdownController::class, 'availableDates']);

    // Betting
    Route::get('/betting/available-draws', [BettingController::class, 'availableDraws']);
    Route::post('/betting/place', [BettingController::class, 'placeBet']);
    Route::get('/betting/list', [BettingController::class, 'listBets']);
    Route::post('/betting/cancel/{id}', [BettingController::class, 'cancelBet']);
    Route::get('/betting/cancelled', [BettingController::class, 'listCancelledBets']);
    Route::post('/betting/cancel-by-ticket/{ticket_id}', [BettingController::class, 'cancelBetByTicketId']);

    // Reports
    Route::get('/reports/tallysheet', [TellerReportController::class, 'tallysheet']);
    Route::get('/reports/sales', [TellerReportController::class, 'sales']);
    Route::get('/reports/detailed-tallysheet', [TellerReportController::class, 'detailedTallysheet']);
    Route::get('/teller/today-sales', [TellerReportController::class, 'todaySales']);
    Route::get('/teller/detailed-tallysheet', [TellerReportController::class, 'detailedTallysheet']);
});

// Dropdown endpoints for frontend dropdowns and calendar
