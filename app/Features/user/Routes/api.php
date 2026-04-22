<?php

use App\Features\user\Controllers\SuperAdmin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('super-admin')
    ->group(function () {
        Route::apiResource('admins', AdminUserController::class);
        Route::put('admins/{admin}/locations', [AdminUserController::class, 'assignLocations']);
    });
