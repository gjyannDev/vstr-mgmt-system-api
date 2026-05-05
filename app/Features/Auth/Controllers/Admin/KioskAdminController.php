<?php

namespace App\Features\Auth\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Features\Auth\Requests\StoreKioskRequest;
use App\Features\Auth\Requests\UpdateKioskRequest;
use App\Features\Auth\Requests\IndexKioskRequest;
use App\Features\Auth\Services\KioskAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Kiosk;
use OpenApi\Annotations as OA;

class KioskAdminController extends Controller
{
  public function __construct(private KioskAdminService $kioskAdminService) {}

  /**
   * @OA\Post(
   *     path="/admin/kiosks",
   *     operationId="createKiosk",
   *     tags={"Kiosks"},
   *     summary="Create kiosk",
   *     description="Create a new kiosk",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function store(StoreKioskRequest $request): JsonResponse
  {
    return $this->kioskAdminService->store($request);
  }

  /**
   * @OA\Get(
   *     path="/admin/kiosks",
   *     operationId="listKiosks",
   *     tags={"Kiosks"},
   *     summary="List kiosks",
   *     description="List kiosks for admin",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function index(IndexKioskRequest $request): JsonResponse
  {
    return $this->kioskAdminService->index($request);
  }

  /**
   * @OA\Get(
   *     path="/admin/kiosks/{kiosk}",
   *     operationId="showKiosk",
   *     tags={"Kiosks"},
   *     summary="Show kiosk",
   *     description="Retrieve a kiosk",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="kiosk",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function show(Request $request, Kiosk $kiosk): JsonResponse
  {
    return $this->kioskAdminService->show($request, $kiosk);
  }

  /**
   * @OA\Put(
   *     path="/admin/kiosks/{kiosk}",
   *     operationId="updateKiosk",
   *     tags={"Kiosks"},
   *     summary="Update kiosk",
   *     description="Update a kiosk",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="kiosk",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function update(UpdateKioskRequest $request, Kiosk $kiosk): JsonResponse
  {
    return $this->kioskAdminService->update($request, $kiosk);
  }

  /**
   * @OA\Post(
   *     path="/admin/kiosks/{kiosk}/regenerate",
   *     operationId="regenerateKiosk",
   *     tags={"Kiosks"},
   *     summary="Regenerate kiosk credentials",
   *     description="Regenerate kiosk secret/credentials",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="kiosk",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function regenerate(Request $request, Kiosk $kiosk): JsonResponse
  {
    return $this->kioskAdminService->regenerate($request, $kiosk);
  }

  /**
   * @OA\Post(
   *     path="/admin/kiosks/{kiosk}/revoke-tokens",
   *     operationId="revokeKioskTokens",
   *     tags={"Kiosks"},
   *     summary="Revoke kiosk tokens",
   *     description="Revoke kiosk tokens",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="kiosk",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function revokeTokens(Request $request, Kiosk $kiosk): JsonResponse
  {
    return $this->kioskAdminService->revokeTokens($request, $kiosk);
  }
}
