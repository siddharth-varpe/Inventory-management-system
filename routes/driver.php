<?php

declare(strict_types=1);

use App\Http\Controllers\DriverTerminalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Driver Terminal Portal Routes (/driver-terminal)
|--------------------------------------------------------------------------
|
| Independent application boundary for Driver Execution & Delivery Operations.
| Route Prefix: /driver-terminal
|
*/

Route::prefix('driver-terminal')->name('driver-terminal.')->group(function () {
    // Unauthenticated Guest Routes (Driver Login System)
    Route::get('/login', [DriverTerminalController::class, 'login'])->name('login');
    Route::post('/login', [DriverTerminalController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('login.post');

    // Authenticated & Scoped Driver Terminal Routes
    Route::middleware(['driver.auth'])->group(function () {
        // Generic /driver-terminal fallback redirect to /driver-terminal/{authenticated_driver_code}
        Route::get('/', function () {
            $driver = request()->attributes->get('current_driver');
            if ($driver) {
                return redirect()->route('driver-terminal.index', ['driver_code' => strtolower($driver->driver_code)]);
            }
            return redirect()->route('driver-terminal.login');
        });

        Route::post('/logout', [DriverTerminalController::class, 'logout'])->name('logout');

        // Driver-Scoped Canonical Routes (/driver-terminal/{driver_code})
        Route::get('/{driver_code}', [DriverTerminalController::class, 'index'])->name('index');
        Route::get('/{driver_code}/profile', [DriverTerminalController::class, 'profile'])->name('profile');
        Route::get('/{driver_code}/driver-profile', [DriverTerminalController::class, 'driverProfile'])->name('driver-profile');
        Route::get('/{driver_code}/notifications', [DriverTerminalController::class, 'notifications'])->name('notifications');
        Route::get('/{driver_code}/deliveries', [DriverTerminalController::class, 'deliveries'])->name('deliveries.index');
        Route::get('/{driver_code}/deliveries/{id}', [DriverTerminalController::class, 'showDelivery'])->name('deliveries.show');
        Route::post('/{driver_code}/deliveries/{id}/accept', [DriverTerminalController::class, 'acceptDelivery'])->name('deliveries.accept');
        Route::post('/{driver_code}/deliveries/{id}/status', [DriverTerminalController::class, 'updateStatus'])->name('deliveries.status');
    });
});

// Canonical /driver fallback route
Route::middleware(['driver.auth'])->get('/driver', function () {
    $driver = request()->attributes->get('current_driver');
    if ($driver) {
        return redirect()->route('driver-terminal.index', ['driver_code' => strtolower($driver->driver_code)]);
    }
    return redirect()->route('driver-terminal.login');
})->name('driver.index');
