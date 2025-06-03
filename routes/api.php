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
use App\Http\Controllers\Api\ReceiptController;

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
    Route::post('/betting/claim-ticket/{ticket_id}', [BettingController::class, 'claimByTicketId']);
    Route::get('/betting/claimed', [BettingController::class, 'listClaimedBets']);
    Route::get('/betting/hits', [BettingController::class, 'listHitBets']);

    // Receipt API (Cart System)
    Route::get('/receipts/draft', [ReceiptController::class, 'getDraft']);
    Route::get('/receipts/find', [ReceiptController::class, 'findByReceiptNumber']);
    Route::get('/receipts', [ReceiptController::class, 'index']);
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show']);
    Route::post('/receipts/{receipt}/bets', [ReceiptController::class, 'addBet']);
    Route::delete('/receipts/{receipt}/bets/{bet}', [ReceiptController::class, 'removeBet']);
    Route::put('/receipts/{receipt}/bets/{bet}', [ReceiptController::class, 'updateBet']);
    Route::post('/receipts/{receipt}/place', [ReceiptController::class, 'placeReceipt']);
    Route::post('/receipts/{receipt}/cancel', [ReceiptController::class, 'cancelReceipt']);


    // Reports
    Route::get('/reports/tallysheet', [TellerReportController::class, 'tallysheet']);
    Route::get('/reports/sales', [TellerReportController::class, 'sales']);
    Route::get('/reports/detailed-tallysheet', [TellerReportController::class, 'detailedTallysheet']);
    Route::get('/teller/today-sales', [TellerReportController::class, 'todaySales']);
    Route::get('/teller/detailed-tallysheet', [TellerReportController::class, 'd tailedTallysheet']);
    Route::get('/teller/commission-report', [TellerReportController::class, 'commissionReport']);
});

// Dropdown endpoints for frontend dropdowns and calendar
