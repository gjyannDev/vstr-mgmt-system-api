<?php

namespace App\Features\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthMvpController extends Controller
{
  use ApiResponse;

  /**
   * @OA\Get(
   *     path="/auth/session-check",
   *     operationId="sessionCheck",
   *     tags={"Authentication"},
   *     summary="Session check",
   *     description="Check authenticated session",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function sessionCheck(Request $request): JsonResponse
  {
    return $this->successResponse('Authenticated access works.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
    ]);
  }

  /**
   * @OA\Get(
   *     path="/admin/ping",
   *     operationId="adminPing",
   *     tags={"Authentication"},
   *     summary="Admin ping",
   *     description="Ping for admin routes",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function adminPing(Request $request): JsonResponse
  {
    return $this->successResponse('Admin route access granted.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
    ]);
  }

  /**
   * @OA\Get(
   *     path="/kiosk/ping",
   *     operationId="kioskPing",
   *     tags={"Authentication"},
   *     summary="Kiosk ping",
   *     description="Ping for kiosk routes",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function kioskPing(Request $request): JsonResponse
  {
    return $this->successResponse('Kiosk route access granted.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
    ]);
  }

  /**
   * @OA\Post(
   *     path="/kiosk/location-check",
   *     operationId="locationCheck",
   *     tags={"Authentication"},
   *     summary="Location check",
   *     description="Check location lock for kiosk",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function locationCheck(Request $request): JsonResponse
  {
    return $this->successResponse('Location lock applied.', [
      'user_id' => $request->user()?->id,
      'role' => $request->user()?->role,
      'location_id' => $request->input('location_id'),
    ]);
  }
}
