<?php

use App\Features\Location\Controllers\SuperAdmin\LocationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:super_admin,admin'])
    ->prefix('super-admin')
    ->group(function () {
        Route::get('locations/list', [LocationController::class, 'listSimple']);
        Route::apiResource('locations', LocationController::class);
    });
