<?php

namespace App\Features\Location\Services;

use App\Features\Location\Repository\LocationRepository;
use App\Features\Location\Requests\StoreLocationRequest;
use App\Features\Location\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationService
{
  use ApiResponse;

  public function __construct(private LocationRepository $locationRepo) {}

  public function index(Request $request): JsonResponse
  {
    $search = $request->query('search');
    $locations = $this->locationRepo->list(is_string($search) ? $search : null);

    return $this->successResponse('Locations fetched successfully.', [
      'locations' => $locations,
    ]);
  }

  public function store(StoreLocationRequest $request): JsonResponse
  {
    $location = $this->locationRepo->createLocation($request->validated());

    return $this->successResponse('Location created successfully.', [
      'location' => $location,
    ], 201);
  }

  public function show(Location $location): JsonResponse
  {
    return $this->successResponse('Location fetched successfully.', [
      'location' => $location,
    ]);
  }

  public function update(UpdateLocationRequest $request, Location $location): JsonResponse
  {
    $updated = $this->locationRepo->updateLocation($location, $request->validated());

    return $this->successResponse('Location updated successfully.', [
      'location' => $updated,
    ]);
  }

  public function destroy(Location $location): JsonResponse
  {
    $this->locationRepo->deleteLocation($location);

    return $this->successResponse('Location deleted successfully.');
  }
}
