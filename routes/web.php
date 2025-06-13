<?php

use App\Livewire\Bets\ListBets;
use App\Livewire\WinningReport;
use App\Livewire\AdminDashboard;
use App\Livewire\Draws\EditDraw;
use App\Livewire\Users\EditUser;
use App\Livewire\Users\ListUsers;
use App\Livewire\Draws\CreateDraw;
use App\Livewire\Users\CreateUser;
use App\Livewire\Draws\ManageDraws;
use App\Livewire\Receipt\ViewReceipt;
use Illuminate\Support\Facades\Route;
use App\Livewire\Receipt\ListReceipts;
use App\Livewire\Reports\SalesSummary;
use App\Livewire\Draws\ViewDrawDetails;

use App\Livewire\BetRatios\ListBetRatio;
use App\Livewire\Coordinator\EditTeller;


use App\Livewire\Reports\ReportByTeller;
use App\Livewire\Locations\ListLocations;
use App\Livewire\Coordinator\CreateTeller;
use App\Livewire\Coordinator\ManageTellers;
use App\Livewire\Reports\TellerSalesReport;
use App\Livewire\Commissions\ListCommission;
use App\Livewire\Reports\TellerSalesSummary;
use App\Livewire\Reports\ReportByCoordinator;
use App\Livewire\LowWinNumbers\ListLowWinNumbers;
use App\Livewire\WinningAmount\ListWinningAmount;
use App\Livewire\Coordinator\CoordinatorDashboard;
use App\Livewire\SoldOutNumbers\ListSoldOutNumbers;
use App\Livewire\Reports\Coordinator\TellerBetsReport;
use App\Livewire\Reports\Coordinator\CoordinatorSalesSummary;
use App\Livewire\Reports\Coordinator\CoordinatorTellerSalesSummary;
use App\Livewire\Coordinator\Reports\WinningReport as CoordinatorWinningReport;

// Guest users are redirected to login
Route::get('/', function () {
    return redirect()->route('login');
})->middleware('guest');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Redirect to appropriate dashboard based on role
    Route::get('/dashboard', function() {

            switch(auth()->user()->role){
                case 'admin':
                    return redirect()->route('main.dashboard');
                case 'coordinator':

                    return redirect()->route('coordinator.dashboard');
                default:
                    return redirect()->route('login');
            }
    })->name('dashboard');

    // Admin Dashboard
    Route::get('/main/dashboard', AdminDashboard::class)->name('main.dashboard')->middleware('can:admin');




    Route::prefix('/manage')->name('manage.')->middleware('can:admin')->group(function(){
        Route::get('locations', ListLocations::class)->name('locations');

        Route::get('users', ListUsers::class)->name('users');
        Route::get('users/create', CreateUser::class)->name('users.create');
        Route::get('users/{user}/edit', EditUser::class)->name('users.edit');

        Route::get('draws', ManageDraws::class)->name('draws');
        Route::get('draws/create', CreateDraw::class)->name('draws.create');
        Route::get('draws/{draw}/edit', EditDraw::class)->name('draws.edit');
        Route::get('draws/{draw}/view', ViewDrawDetails::class)->name('draws.view');

        Route::get('winning-amounts', ListWinningAmount::class)->name('winning-amounts');


        //
        Route::get('bets', ListBets::class)->name('bets');
        Route::get('bet-ratios', ListBetRatio::class)->name('bet-ratios');
        Route::get('commissions', ListCommission::class)->name('commissions');
        Route::get('low-win-numbers', ListLowWinNumbers::class)->name('low-win-numbers');
        Route::get('sold-out-numbers', ListSoldOutNumbers::class)->name('sold-out-numbers');

        // Receipts Management
        Route::get('receipts', ListReceipts::class)->name('receipts');
        Route::get('receipts/{receipt}/view', ViewReceipt::class)->name('receipts.view');

    });

    Route::prefix('/reports')->name('reports.')->middleware('can:admin')->group(function(){
        //coordinator
        Route::get('coordinator/summary', CoordinatorSalesSummary::class)->name('summary');
        Route::get('coordinator/teller-sales-summary/{coordinator_id}', CoordinatorTellerSalesSummary::class)->name('teller-sales-summary');
        Route::get('coordinator/teller-bets-report/{teller_id}/{date?}', TellerBetsReport::class)->name('teller-bets-report');
        Route::get('tellers', TellerSalesReport::class)->name('tellers');
        Route::get('tellers/summary', TellerSalesSummary::class)->name('tellers-summary');
        Route::get('winning-report', WinningReport::class)->name('winning-report');
    });

    // Coordinator Routes
    Route::middleware(['auth:sanctum','can:coordinator'])->prefix('/coordinator')->name('coordinator.')->group(function() {
        // Dashboard
        Route::get('/dashboard', CoordinatorDashboard::class)->name('dashboard');

        // Teller Management
        Route::get('/tellers',ManageTellers::class)->name('tellers');
        Route::get('/tellers/create',CreateTeller::class)->name('tellers.create');
        Route::get('/tellers/{record}/edit',EditTeller::class)->name('tellers.edit');


        // Game Management
        Route::get('/draws', \App\Livewire\Coordinator\ViewDraws::class)->name('draws');
        Route::get('/winning-amounts', \App\Livewire\Coordinator\ListWinningAmount::class)->name('winning-amounts');
        Route::get('/bet-ratios', \App\Livewire\Coordinator\ListBetRatios::class)->name('coordinator.bet-ratios');
        Route::get('/sold-out-numbers', \App\Livewire\Coordinator\ListSoldOutNumbers::class)->name('coordinator.sold-out-numbers');
        Route::get('/low-win-numbers', \App\Livewire\Coordinator\ListLowWinNumbers::class)->name('coordinator.low-win-numbers');
        Route::get('/bets', \App\Livewire\Coordinator\ManageBets::class)->name('coordinator.bets');

        // Reports
        Route::prefix('/reports')->name('reports.')->group(function() {
            // Route::get('/daily', \App\Livewire\Reports\Coordinator\CoordinatorSalesSummary::class)->name('daily');
            // Route::get('/teller', \App\Livewire\Reports\Coordinator\CoordinatorTellerSalesSummary::class)->name('teller');

            // Report routes for coordinator
            Route::get('/teller-sales-summary', \App\Livewire\Reports\Coordinator\CoordinatorTellerSalesSummary::class)->name('teller-sales-summary');
            Route::get('/teller-bets-report/{teller_id}/{date?}', \App\Livewire\Reports\Coordinator\TellerBetsReport::class)->name('teller-bets-report');
            Route::get('/tellers', \App\Livewire\Reports\Coordinator\TellerSalesReport::class)->name('tellers');
            Route::get('/winning', \App\Livewire\Reports\Coordinator\CoordinatorWinningReport::class)->name('winning');
        });
    });


});
