<?php

declare(strict_types=1);

use App\Http\Controllers\DriverTerminalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Driver Terminal Portal Routes
|--------------------------------------------------------------------------
|
| Independent application boundary for Driver Execution & Delivery Operations.
| Route Prefix: /driver
|
*/

/*
|--------------------------------------------------------------------------
| Canonical Driver Terminal Routes (/driver & /driver-terminal)
|--------------------------------------------------------------------------
*/
Route::middleware(['driver.auth'])->get('/driver', [DriverTerminalController::class, 'index'])->name('driver.index');

/*
|--------------------------------------------------------------------------
| Canonical Driver Terminal Route Group (/driver-terminal)
|--------------------------------------------------------------------------
*/
Route::prefix('driver-terminal')->name('driver-terminal.')->group(function () {
    // Unauthenticated Guest Routes (Authentication - NO OTP)
    Route::get('/login', [DriverTerminalController::class, 'login'])->name('login');
    Route::post('/login', [DriverTerminalController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('login.post');

    // Authenticated Driver Terminal Routes
    Route::middleware(['driver.auth'])->group(function () {
        Route::get('/', [DriverTerminalController::class, 'index'])->name('index');
        Route::post('/logout', [DriverTerminalController::class, 'logout'])->name('logout');
        Route::get('/deliveries', [DriverTerminalController::class, 'deliveries'])->name('deliveries');
        Route::get('/delivery/{id}', [DriverTerminalController::class, 'showDelivery'])->name('delivery.show');
        Route::get('/profile', [DriverTerminalController::class, 'profile'])->name('profile');
        Route::get('/notifications', [DriverTerminalController::class, 'notifications'])->name('notifications');
    });
});
