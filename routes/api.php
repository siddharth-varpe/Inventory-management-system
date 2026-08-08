<?php

declare(strict_types=1);

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Enterprise API Routes (v1)
|--------------------------------------------------------------------------
|
| Stateless API endpoints for mobile & external integrations.
|
*/

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/health', function (): JsonResponse {
        return response()->json([
            'status' => 'ok',
            'system' => 'StockManager Enterprise ERP API',
            'version' => 'v1',
            'timestamp' => now()->toIso8601String(),
        ]);
    });
});
