<?php

namespace App\Features\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthMvpController extends Controller
{
  use ApiResponse;

  public function sessionCheck(Request $request): JsonResponse
  {
    return $this->successResponse('Authenticated access works.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
    ]);
  }

  public function adminPing(Request $request): JsonResponse
  {
    return $this->successResponse('Admin route access granted.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
    ]);
  }

  public function kioskPing(Request $request): JsonResponse
  {
    return $this->successResponse('Kiosk route access granted.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
    ]);
  }

  public function locationCheck(Request $request): JsonResponse
  {
    return $this->successResponse('Location lock applied.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
      'location_id' => $request->integer('location_id'),
    ]);
  }
}
