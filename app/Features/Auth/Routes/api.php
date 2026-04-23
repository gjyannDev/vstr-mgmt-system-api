<?php

use Illuminate\Support\Facades\Route;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Auth\Controllers\AuthMvpController;
use App\Features\Auth\Controllers\KioskAuthController;
use App\Features\Auth\Controllers\KioskVisitController;
use App\Features\Auth\Controllers\Admin\KioskAdminController;

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
  Route::get('/kiosks', [KioskAdminController::class, 'index']);
  Route::post('/kiosks', [KioskAdminController::class, 'store']);
  Route::put('/kiosks/{kiosk}', [KioskAdminController::class, 'update']);
  Route::get('/kiosks/{kiosk}', [KioskAdminController::class, 'show']);
  Route::post('/kiosks/{kiosk}/regenerate', [KioskAdminController::class, 'regenerate']);
  Route::post('/kiosks/{kiosk}/revoke-tokens', [KioskAdminController::class, 'revokeTokens']);
});

Route::middleware(['auth:sanctum', 'ability:kiosk:access'])->prefix('kiosk')->group(function () {
  Route::get('/ping', [AuthMvpController::class, 'kioskPing']);
  Route::post('/location-check', [AuthMvpController::class, 'locationCheck'])->middleware('location.lock');
});

Route::post('/kiosk/activate', [KioskAuthController::class, 'activate']);

Route::middleware(['auth:sanctum', 'abilities:kiosk', 'kiosk.device'])->prefix('kiosk')->group(function () {
  Route::get('/me', [KioskAuthController::class, 'me']);
  Route::post('/logout', [KioskAuthController::class, 'logout']);
  Route::post('/visits', [KioskVisitController::class, 'store']);
});
