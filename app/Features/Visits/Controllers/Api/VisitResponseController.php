<?php

namespace App\Features\Visits\Controllers\Api;

use App\Features\Visits\Requests\StoreVisitResponseRequest;
use App\Features\Visits\Services\VisitResponseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class VisitResponseController extends Controller
{
  public function __construct(private VisitResponseService $service) {}

  /**
   * @OA\Post(
   *     path="/kiosk/visit-responses",
   *     operationId="storeVisitResponse",
   *     tags={"VisitResponses"},
   *     summary="Store a visit response",
   *     description="Create a new visit response from kiosk",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function store(StoreVisitResponseRequest $request): JsonResponse
  {
    return $this->service->store($request);
  }

  /**
   * @OA\Get(
   *     path="/kiosk/visit-responses/{visitId}",
   *     operationId="getVisitResponse",
   *     tags={"VisitResponses"},
   *     summary="Get visit response",
   *     description="Retrieve visit response by id",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="visitId",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function show(string $visitId): JsonResponse
  {
    return $this->service->showById($visitId);
  }

  /**
   * @OA\Get(
   *     path="/visits/by-qr/{qrCode}",
   *     operationId="getVisitByQr",
   *     tags={"VisitResponses"},
   *     summary="Get visit by QR",
   *     description="Lookup a visit by its QR code",
   *     @OA\Parameter(
   *         name="qrCode",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function showByQr(string $qrCode): JsonResponse
  {
    return $this->service->showByQr($qrCode);
  }

  /**
   * @OA\Get(
   *     path="/kiosk/visits/by-id/{idNumber}",
   *     operationId="getVisitByIdNumber",
   *     tags={"VisitResponses"},
   *     summary="Get visit by ID number",
   *     description="Lookup a visit by visitor ID number",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="idNumber",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function showByIdNumber(Request $request, string $idNumber): JsonResponse
  {
    return $this->service->showByIdNumber($request, $idNumber);
  }

  /**
   * @OA\Patch(
   *     path="/kiosk/visits/{visitId}/checkout",
   *     operationId="checkoutVisit",
   *     tags={"VisitResponses"},
   *     summary="Checkout a visit",
   *     description="Marks a visit as checked out",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="visitId",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="string")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function checkout(Request $request, string $visitId): JsonResponse
  {
    return $this->service->checkout($request, $visitId);
  }
}
