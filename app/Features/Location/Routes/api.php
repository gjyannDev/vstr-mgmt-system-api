<?php

use App\Features\Location\Controllers\SuperAdmin\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('super-admin')
    ->group(function () {
        // lightweight list for select inputs (id + name)
        Route::get('locations/list', [LocationController::class, 'listSimple']);
        Route::apiResource('locations', LocationController::class);
    });
