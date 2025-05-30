<?php

use App\Livewire\AdminDashboard;
use App\Livewire\Users\EditUser;
use App\Livewire\Users\ListUsers;
use App\Livewire\Users\CreateUser;
use App\Livewire\Draws\ManageDraws;
use App\Livewire\Draws\CreateDraw;
use App\Livewire\Draws\EditDraw;
use Illuminate\Support\Facades\Route;
use App\Livewire\Locations\ListLocations;


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



    });
   



});

 
