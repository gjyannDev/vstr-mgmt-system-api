<?php

namespace App\Features\Auth\Controllers;

use App\Features\Auth\Requests\ActivateKioskRequest;
use App\Features\Auth\Services\KioskAuthService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class KioskAuthController extends Controller
{
  public function __construct(private KioskAuthService $kioskAuthService) {}

  /**
   * @OA\Post(
   *     path="/kiosk/activate",
   *     operationId="activateKiosk",
   *     tags={"Kiosk"},
   *     summary="Activate kiosk",
   *     description="Activate a kiosk device",
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function activate(ActivateKioskRequest $request): JsonResponse
  {
    return $this->kioskAuthService->activate($request);
  }

  /**
   * @OA\Get(
   *     path="/kiosk/me",
   *     operationId="getKioskCurrentUser",
   *     tags={"Kiosk"},
   *     summary="Get current kiosk user",
   *     description="Returns authenticated kiosk user",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function me(Request $request): JsonResponse
  {
    return $this->kioskAuthService->me($request);
  }

  /**
   * @OA\Post(
   *     path="/kiosk/logout",
   *     operationId="logoutKiosk",
   *     tags={"Kiosk"},
   *     summary="Logout kiosk",
   *     description="Logout kiosk session",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function logout(Request $request): JsonResponse
  {
    return $this->kioskAuthService->logout($request);
  }
}
