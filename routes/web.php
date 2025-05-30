<?php

use App\Livewire\Bets\ListBets;
use App\Livewire\AdminDashboard;
use App\Livewire\Draws\EditDraw;
use App\Livewire\Users\EditUser;
use App\Livewire\Users\ListUsers;
use App\Livewire\Draws\CreateDraw;
use App\Livewire\Users\CreateUser;
use App\Livewire\Draws\ManageDraws;
use Illuminate\Support\Facades\Route;
use App\Livewire\Reports\SalesSummary;
use App\Livewire\Reports\ReportByTeller;
use App\Livewire\BetRatios\ListBetRatios;
use App\Livewire\Locations\ListLocations;
use App\Livewire\Commissions\ListCommission;
use App\Livewire\Reports\ReportByCoordinator;
use App\Livewire\LowWinNumbers\ListLowWinNumbers;
use App\Livewire\Reports\Coordinator\TellerBetsReport;
use App\Livewire\Reports\Coordinator\CoordinatorSalesSummary;
use App\Livewire\Reports\Coordinator\CoordinatorTellerSalesSummary;

Route::get('/', function () {
    return route('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {


    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');

    Route::prefix('/manage')->name('manage.')->group(function(){
        Route::get('locations', ListLocations::class)->name('locations');

        Route::get('users', ListUsers::class)->name('users');
        Route::get('users/create', CreateUser::class)->name('users.create');
        Route::get('users/{user}/edit', EditUser::class)->name('users.edit');

        Route::get('draws', ManageDraws::class)->name('draws');
        Route::get('draws/create', CreateDraw::class)->name('draws.create');
        Route::get('draws/{draw}/edit', EditDraw::class)->name('draws.edit');


        //
        Route::get('bets', ListBets::class)->name('bets');
        Route::get('bet-ratios', ListBetRatios::class)->name('bet-ratios');
        Route::get('commissions', ListCommission::class)->name('commissions');
        Route::get('low-win-numbers', ListLowWinNumbers::class)->name('low-win-numbers');





    });

    Route::prefix('/reports')->name('reports.')->group(function(){
        //coordinator
        Route::get('coordinator/summary', CoordinatorSalesSummary::class)->name('summary');
        Route::get('coordinator/teller-sales-summary/{coordinator_id}', CoordinatorTellerSalesSummary::class)->name('teller-sales-summary');
        Route::get('coordinator/teller-bets-report/{teller_id}/{date?}', TellerBetsReport::class)->name('teller-bets-report');

    });



});


