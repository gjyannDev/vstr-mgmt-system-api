<?php

use Illuminate\Support\Facades\Route;
use App\Features\Auth\Controllers\AuthController;

Route::prefix('auth')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);

  Route::middleware(['auth:sanctum', 'ability:auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
  });
});

Route::middleware(['auth:sanctum', 'ability:role:super_admin,role:admin'])->prefix('admin')->group(function () {
  // Register admin module routes here.
  // Example: require base_path('app/Features/Visitor/Routes/admin.php');
});

Route::middleware(['auth:sanctum', 'ability:role:super_admin,role:kiosk'])->prefix('kiosk')->group(function () {
  // Register kiosk module routes here.
});

Route::middleware(['auth:sanctum', 'ability:role:super_admin,role:customer'])->prefix('customer')->group(function () {
  // Register customer module routes here.
});
