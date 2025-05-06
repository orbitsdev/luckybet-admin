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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Mobile API endpoints (based on LuckyBet_Mobile_API.md)
    Route::get('/schedules/available', [BettingController::class, 'availableSchedules']);
    Route::get('/game-types', [GameTypeController::class, 'index']);
    Route::post('/bets', [BettingController::class, 'placeBet']);
    Route::post('/bets/{id}/cancel', [BettingController::class, 'cancelBet']);
    Route::get('/bets', [BettingController::class, 'listBets']);
    
    // Legacy API endpoints (to be migrated)
    Route::get('/draws/available', [BettingController::class, 'availableDraws']);
    Route::get('/draws/by-game-type', [BettingController::class, 'availableDrawsByGameType']);
    Route::post('/teller/bet', [BettingController::class, 'placeBet']); // Duplicate of /bets
    Route::get('/teller/bets', [BettingController::class, 'listBets']); // Duplicate of /bets
    Route::post('/teller/bet/cancel', [BettingController::class, 'cancelBet']); // To be replaced by /bets/{id}/cancel

    // Claims, Results and Reports
    Route::post('/teller/claim', [ClaimController::class, 'submit']);
    Route::get('/teller/claims', [ClaimController::class, 'index']);
    Route::get('/teller/tally-sheet', [TallySheetController::class, 'index']);
    Route::post('/coordinator/result', [ResultController::class, 'submit']);
    Route::get('/results', [ResultController::class, 'index']);
    Route::get('/coordinator/summary-report', [CoordinatorReportController::class, 'index']);

    // Number Flag routes
    Route::apiResource('number-flags', NumberFlagController::class);
});



