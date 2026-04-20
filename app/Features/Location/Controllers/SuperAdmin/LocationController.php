<?php

namespace App\Features\Location\Controllers\SuperAdmin;

use App\Features\Location\Requests\StoreLocationRequest;
use App\Features\Location\Requests\UpdateLocationRequest;
use App\Features\Location\Services\LocationService;
use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
  public function __construct(private LocationService $locationService) {}

  public function index(Request $request): JsonResponse
  {
    return $this->locationService->index($request);
  }

  public function store(StoreLocationRequest $request): JsonResponse
  {
    return $this->locationService->store($request);
  }

  public function show(Location $location): JsonResponse
  {
    return $this->locationService->show($location);
  }

  public function update(UpdateLocationRequest $request, Location $location): JsonResponse
  {
    return $this->locationService->update($request, $location);
  }

  public function destroy(Location $location): JsonResponse
  {
    return $this->locationService->destroy($location);
  }
}
