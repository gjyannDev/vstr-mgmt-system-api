<?php

use App\Features\Visits\Controllers\Admin\VisitTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'ability:admin:access'])
  ->prefix('admin')
  ->group(function () {
    Route::post('locations/{location}/visit-types/{visit_type}/form-fields', [VisitTypeController::class, 'storeFormField']);
    Route::apiResource('locations.visit-types', VisitTypeController::class);
  });
