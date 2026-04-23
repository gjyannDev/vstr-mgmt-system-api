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

class VisitTypeController extends Controller
{
  public function __construct(private VisitTypeService $visitTypeService) {}

  public function index(Request $request, Location $location): JsonResponse
  {
    return $this->visitTypeService->index($request, $location);
  }

  public function store(StoreVisitTypeRequest $request, Location $location): JsonResponse
  {
    return $this->visitTypeService->store($request, $location);
  }

  public function show(Request $request, Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->show($request, $location, $visitType);
  }

  public function update(UpdateVisitTypeRequest $request, Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->update($request, $location, $visitType);
  }

  public function destroy(Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->destroy($location, $visitType);
  }

  public function storeFormField(StoreFormFieldRequest $request, Location $location, VisitType $visitType): JsonResponse
  {
    return $this->visitTypeService->createFormField($request, $location, $visitType);
  }
}
