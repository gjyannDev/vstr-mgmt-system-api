<?php

use Illuminate\Support\Facades\Route;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Auth\Controllers\AuthMvpController;

Route::prefix('auth')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);

  Route::middleware(['auth:sanctum', 'ability:auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/session-check', [AuthMvpController::class, 'sessionCheck']);
  });
});

Route::middleware(['auth:sanctum', 'ability:admin:access'])->prefix('admin')->group(function () {
  Route::get('/ping', [AuthMvpController::class, 'adminPing']);
});

Route::middleware(['auth:sanctum', 'ability:kiosk:access'])->prefix('kiosk')->group(function () {
  Route::get('/ping', [AuthMvpController::class, 'kioskPing']);
  Route::post('/location-check', [AuthMvpController::class, 'locationCheck'])->middleware('location.lock');
});
