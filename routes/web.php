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
use Illuminate\Support\Facades\Route;
use App\Livewire\Reports\SalesSummary;
use App\Livewire\Draws\ViewDrawDetails;
use App\Livewire\Reports\ReportByTeller;
use App\Livewire\BetRatios\ListBetRatios;
use App\Livewire\Locations\ListLocations;
use App\Livewire\Reports\TellerSalesReport;
use App\Livewire\Commissions\ListCommission;
use App\Livewire\Reports\TellerSalesSummary;
use App\Livewire\Reports\ReportByCoordinator;
use App\Livewire\LowWinNumbers\ListLowWinNumbers;
use App\Livewire\WinningAmount\ListWinningAmount;
use App\Livewire\SoldOutNumbers\ListSoldOutNumbers;
use App\Livewire\Reports\Coordinator\TellerBetsReport;
use App\Livewire\Reports\Coordinator\CoordinatorSalesSummary;
use App\Livewire\Reports\Coordinator\CoordinatorTellerSalesSummary;
use App\Livewire\Coordinator\CoordinatorDashboard;
use App\Livewire\Coordinator\ManageTellers;
use App\Livewire\Coordinator\ManageBets;
use App\Livewire\Coordinator\ViewDraws;

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
    Route::get('/', function() {
        if (auth()->check()) {
            if (auth()->user()->role === 'coordinator') {
                return redirect()->route('coordinator.dashboard');
            } elseif (auth()->user()->role === 'admin') {
                return redirect()->route('dashboard');
            } else {
                // Handle other roles or default case
                return redirect()->route('login')->with('error', 'No dashboard available for your role.');
            }
        }
        return redirect()->route('login');
    });
    
    // Admin Dashboard
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard')->middleware('can:admin');

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
        Route::get('bet-ratios', ListBetRatios::class)->name('bet-ratios');
        Route::get('commissions', ListCommission::class)->name('commissions');
        Route::get('low-win-numbers', ListLowWinNumbers::class)->name('low-win-numbers');
        Route::get('sold-out-numbers', ListSoldOutNumbers::class)->name('sold-out-numbers');





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
    Route::middleware(['auth:sanctum', 'can:coordinator'])->prefix('/coordinator')->name('coordinator.')->group(function() {
        Route::get('/dashboard', CoordinatorDashboard::class)->name('dashboard');
        Route::get('/tellers', ManageTellers::class)->name('tellers');
        Route::get('/bets', ManageBets::class)->name('bets');
        Route::get('/draws', ViewDraws::class)->name('draws');
        
        // Coordinator Reports
        Route::prefix('/reports')->name('reports.')->group(function() {
            Route::get('/daily', function() { 
                return view('livewire.coordinator.reports.daily'); 
            })->name('daily');
            Route::get('/teller', function() { 
                return view('livewire.coordinator.reports.teller'); 
            })->name('teller');
        });
    });


});
