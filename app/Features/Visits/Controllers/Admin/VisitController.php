<?php

namespace App\Features\Visits\Controllers\Admin;

use App\Features\Visits\Services\VisitService;
use App\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class VisitController extends Controller
{
  public function __construct(private VisitService $visitService) {}

  /**
   * @OA\Get(
   *     path="/admin/locations/{location}/visits",
   *     operationId="adminListVisits",
   *     tags={"Visits"},
   *     summary="List visits (admin)",
   *     description="List visits for a location (admin)",
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
    return $this->visitService->index($request, $location);
  }
}
