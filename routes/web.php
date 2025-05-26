<?php

use App\Livewire\AdminDashboard;
use Illuminate\Support\Facades\Route;
use App\Livewire\Locations\ListLocations;
use App\Livewire\Users\ListUsers;
use App\Livewire\Users\CreateUser;
use App\Livewire\Users\EditUser;
// use App\Livewire\Tellers\ListTellers;
// use App\Livewire\Tellers\CreateTeller;
// use App\Livewire\Tellers\EditTeller;

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

        // // teller management 
        // Route::get('tellers', ListTellers::class)->name('tellers');
        // Route::get('tellers/create', CreateTeller::class)->name('tellers.create');
        // Route::get('tellers/{teller}/edit', EditTeller::class)->name('tellers.edit');
    });
   



});

 
