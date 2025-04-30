<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClaimController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\BettingController;
use App\Http\Controllers\Api\TallySheetController;
use App\Http\Controllers\Api\CoordinatorReportController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/draws/available', [BettingController::class, 'availableDraws']);
    Route::post('/teller/bet', [BettingController::class, 'placeBet']);

    Route::get('/teller/bets', [BettingController::class, 'listBets']);
    Route::post('/teller/bet/cancel', [BettingController::class, 'cancelBet']);

    Route::post('/teller/claim', [ClaimController::class, 'submit']);
    Route::get('/teller/claims', [ClaimController::class, 'index']);

    Route::get('/teller/tally-sheet', [TallySheetController::class, 'index']);


    Route::post('/coordinator/result', [ResultController::class, 'submit']);
    Route::get('/results', [ResultController::class, 'index']);

    Route::get('/coordinator/summary-report', [CoordinatorReportController::class, 'index']);
});


