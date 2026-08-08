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

Route::middleware(['auth', 'verified'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/', [DriverTerminalController::class, 'index'])->name('index');
    Route::get('/live-sync', [DriverTerminalController::class, 'liveSync'])->name('live-sync');
    Route::post('/trips/{transportTrip}/accept', [DriverTerminalController::class, 'acceptTrip'])->name('accept-trip');
    Route::post('/requests/{transportRequest}/update-status', [DriverTerminalController::class, 'updateStatus'])->name('update-status');
});
