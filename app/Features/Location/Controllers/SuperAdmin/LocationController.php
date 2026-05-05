<?php

namespace App\Features\Location\Controllers\SuperAdmin;

use App\Features\Location\Requests\StoreLocationRequest;
use App\Features\Location\Requests\UpdateLocationRequest;
use App\Features\Location\Services\LocationService;
use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class LocationController extends Controller
{
  public function __construct(private LocationService $locationService) {}

  /**
   * @OA\Get(
   *     path="/super-admin/locations",
   *     operationId="listLocations",
   *     tags={"Locations"},
   *     summary="List locations",
   *     description="List locations (super-admin/admin)",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function index(Request $request): JsonResponse
  {
    return $this->locationService->index($request);
  }

  /**
   * @OA\Get(
   *     path="/super-admin/locations/list",
   *     operationId="listSimpleLocations",
   *     tags={"Locations"},
   *     summary="List simple locations",
   *     description="Return a simple list of locations",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function listSimple(Request $request): JsonResponse
  {
    return $this->locationService->listSimple($request);
  }

  /**
   * @OA\Post(
   *     path="/super-admin/locations",
   *     operationId="createLocation",
   *     tags={"Locations"},
   *     summary="Create location",
   *     description="Create a new location",
   *     security={{"sanctum": {}}},
   *     @OA\Response(response=200, description="Success")
   * )
   */
  public function store(StoreLocationRequest $request): JsonResponse
  {
    return $this->locationService->store($request);
  }

  /**
   * @OA\Get(
   *     path="/super-admin/locations/{location}",
   *     operationId="showLocation",
   *     tags={"Locations"},
   *     summary="Show location",
   *     description="Retrieve location details",
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
  public function show(Location $location): JsonResponse
  {
    return $this->locationService->show($location);
  }

  /**
   * @OA\Put(
   *     path="/super-admin/locations/{location}",
   *     operationId="updateLocation",
   *     tags={"Locations"},
   *     summary="Update location",
   *     description="Update a location",
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
  public function update(UpdateLocationRequest $request, Location $location): JsonResponse
  {
    return $this->locationService->update($request, $location);
  }

  /**
   * @OA\Delete(
   *     path="/super-admin/locations/{location}",
   *     operationId="deleteLocation",
   *     tags={"Locations"},
   *     summary="Delete location",
   *     description="Delete a location",
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
  public function destroy(Location $location): JsonResponse
  {
    return $this->locationService->destroy($location);
  }
}
