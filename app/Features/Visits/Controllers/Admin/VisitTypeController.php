<?php

namespace App\Features\Visits\Controllers\Admin;

use App\Features\Visits\Services\VisitTypeService;
use App\Features\Visits\Requests\StoreVisitTypeRequest;
use App\Features\Visits\Requests\UpdateVisitTypeRequest;
use App\Features\Visits\Requests\StoreFormFieldRequest;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\VisitType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class VisitTypeController extends Controller
{
  public function __construct(private VisitTypeService $visitTypeService) {}

  /**
   * @OA\Get(
   *     path="/admin/locations/{location}/visit-types",
   *     operationId="adminListVisitTypes",
   *     tags={"VisitTypes"},
   *     summary="List visit types (admin)",
   *     description="List visit types for a location (admin)",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   *
   * @OA\Get(
   *     path="/kiosk/locations/{location}/visit-types",
   *     operationId="kioskListVisitTypes",
   *     tags={"VisitTypes"},
   *     summary="List visit types (kiosk)",
   *     description="List visit types for a location (kiosk)",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function index(Request $request, Location $location): JsonResponse
  {
    return $this->visitTypeService->index($request, $location);
  }

  /**
   * @OA\Post(
   *     path="/admin/locations/{location}/visit-types",
   *     operationId="adminStoreVisitType",
   *     tags={"VisitTypes"},
   *     summary="Create visit type",
   *     description="Create a visit type for a location",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function store(StoreVisitTypeRequest $request, Location $location): JsonResponse
  {
    return $this->visitTypeService->store($request, $location);
  }

  /**
   * @OA\Get(
   *     path="/admin/locations/{location}/visit-types/{visit_type}",
   *     operationId="adminShowVisitType",
   *     tags={"VisitTypes"},
   *     summary="Show visit type (admin)",
   *     description="Retrieve a visit type",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Parameter(
   *         name="visit_type",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   *
   * @OA\Get(
   *     path="/kiosk/locations/{location}/visit-types/{visit_type}",
   *     operationId="kioskShowVisitType",
   *     tags={"VisitTypes"},
   *     summary="Show visit type (kiosk)",
   *     description="Retrieve a visit type for kiosk clients",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Parameter(
   *         name="visit_type",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function show(Request $request, Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->show($request, $location, $visitType);
  }

  /**
   * @OA\Put(
   *     path="/admin/locations/{location}/visit-types/{visit_type}",
   *     operationId="adminUpdateVisitType",
   *     tags={"VisitTypes"},
   *     summary="Update visit type",
   *     description="Update a visit type for a location",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Parameter(
   *         name="visit_type",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function update(UpdateVisitTypeRequest $request, Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->update($request, $location, $visitType);
  }

  /**
   * @OA\Delete(
   *     path="/admin/locations/{location}/visit-types/{visit_type}",
   *     operationId="adminDeleteVisitType",
   *     tags={"VisitTypes"},
   *     summary="Delete visit type",
   *     description="Delete a visit type",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Parameter(
   *         name="visit_type",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function destroy(Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->destroy($location, $visitType);
  }

  /**
   * @OA\Post(
   *     path="/admin/locations/{location}/visit-types/{visit_type}/form-fields",
   *     operationId="adminStoreVisitTypeFormField",
   *     tags={"VisitTypes"},
   *     summary="Create form field for visit type",
   *     description="Create a form field for a visit type",
   *     security={{"sanctum": {}}},
   *     @OA\Parameter(
   *         name="location",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Parameter(
   *         name="visit_type",
   *     in="path",
   *     required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function storeFormField(StoreFormFieldRequest $request, Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->createFormField($request, $location, $visitType);
  }
}
