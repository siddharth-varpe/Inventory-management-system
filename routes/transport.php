<?php

declare(strict_types=1);

use App\Http\Controllers\TransportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Transport Department Portal Routes
|--------------------------------------------------------------------------
|
| Independent application boundary for Fleet Logistics & Transport Control Tower.
| Route Prefix: /transport
|
*/

Route::middleware(['auth', 'verified'])->prefix('transport')->name('transport.')->group(function () {
    Route::get('/', [TransportController::class, 'index'])->name('index');
    Route::get('/live-queue', [TransportController::class, 'liveQueue'])->name('live-queue');
    
    // Dedicated Driver Master Endpoints (Phase 1)
    Route::get('/drivers', [TransportController::class, 'indexDrivers'])->name('drivers.index');
    Route::post('/drivers', [TransportController::class, 'storeDriver'])->name('drivers.store');
    Route::get('/drivers/{driver}', [TransportController::class, 'showDriver'])->name('drivers.show');
    Route::put('/drivers/{driver}', [TransportController::class, 'updateDriver'])->name('drivers.update');
    Route::post('/drivers/{driver}/activate', [TransportController::class, 'activateDriver'])->name('drivers.activate');
    Route::post('/drivers/{driver}/deactivate', [TransportController::class, 'deactivateDriver'])->name('drivers.deactivate');
    Route::post('/drivers/{driver}/suspend', [TransportController::class, 'suspendDriver'])->name('drivers.suspend');

    // Dedicated Vehicle Master Endpoints (Phase 2)
    Route::get('/vehicles', [TransportController::class, 'indexVehicles'])->name('vehicles.index');
    Route::post('/vehicles', [TransportController::class, 'storeVehicle'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}', [TransportController::class, 'showVehicle'])->name('vehicles.show');
    Route::put('/vehicles/{vehicle}', [TransportController::class, 'updateVehicle'])->name('vehicles.update');
    Route::post('/vehicles/{vehicle}/activate', [TransportController::class, 'activateVehicle'])->name('vehicles.activate');
    Route::post('/vehicles/{vehicle}/deactivate', [TransportController::class, 'deactivateVehicle'])->name('vehicles.deactivate');
    Route::post('/vehicles/{vehicle}/maintenance', [TransportController::class, 'markVehicleMaintenance'])->name('vehicles.maintenance');
    Route::post('/vehicles/{vehicle}/return-maintenance', [TransportController::class, 'returnVehicleMaintenance'])->name('vehicles.return-maintenance');
    Route::post('/vehicles/{vehicle}/breakdown', [TransportController::class, 'markVehicleBreakdown'])->name('vehicles.breakdown');
    Route::post('/vehicles/{vehicle}/recover-breakdown', [TransportController::class, 'recoverVehicleBreakdown'])->name('vehicles.recover-breakdown');

    // Operational Workflow Endpoints
    Route::post('/{transportRequest}/assign-vehicle', [TransportController::class, 'assignVehicle'])->name('assign-vehicle');
    Route::post('/{transportRequest}/assign-driver', [TransportController::class, 'assignDriver'])->name('assign-driver');
    Route::post('/{transportRequest}/create-trip', [TransportController::class, 'createTrip'])->name('create-trip');
    Route::post('/{transportRequest}/accept-custody', [TransportController::class, 'acceptCustody'])->name('accept-custody');
    Route::post('/{transportRequest}/update-checklist', [TransportController::class, 'updateChecklist'])->name('update-checklist');
    Route::post('/{transportRequest}/dispatch-trip', [TransportController::class, 'dispatchTrip'])->name('dispatch-trip');
    Route::post('/trips/{transportTrip}/close', [TransportController::class, 'closeTrip'])->name('close-trip');
});
