<?php

use App\Features\Visits\Controllers\Admin\VisitTypeController;
use App\Features\Visits\Controllers\Admin\VisitController;
use App\Features\Visits\Controllers\Api\VisitResponseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'ability:admin:access'])
    ->prefix('admin')
    ->group(function () {
        // Visits listing for admin panels (paginated)
        Route::get('locations/{location}/visits', [VisitController::class, 'index']);

        Route::post('locations/{location}/visit-types/{visit_type}/form-fields', [VisitTypeController::class, 'storeFormField']);
        Route::apiResource('locations.visit-types', VisitTypeController::class);
    });

// Kiosk-facing endpoints for visit responses
Route::middleware(['auth:sanctum', 'abilities:kiosk', 'kiosk.device'])->prefix('kiosk')->group(function () {
    Route::post('/visit-responses', [VisitResponseController::class, 'store']);
    Route::get('/visit-responses/{visitId}', [VisitResponseController::class, 'show']);
    // Lookup by visitor id_number and checkout endpoints for kiosk clients
    Route::get('/visits/by-id/{idNumber}', [VisitResponseController::class, 'showByIdNumber']);
    Route::patch('/visits/{visitId}/checkout', [VisitResponseController::class, 'checkout']);
    // Kiosk clients can fetch visit types and their form fields for a location
    Route::get('/locations/{location}/visit-types', [VisitTypeController::class, 'index']);
    Route::get('/locations/{location}/visit-types/{visit_type}', [VisitTypeController::class, 'show']);
});

// Public/admin lookup by QR (protected by auth if needed)
Route::get('/visits/by-qr/{qrCode}', [VisitResponseController::class, 'showByQr']);
