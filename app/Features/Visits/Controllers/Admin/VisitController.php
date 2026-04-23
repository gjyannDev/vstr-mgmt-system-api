<?php

namespace App\Features\Visits\Controllers\Admin;

use App\Features\Visits\Services\VisitService;
use App\Models\Location;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
  public function __construct(private VisitService $visitService) {}

  public function index(Request $request, Location $location): JsonResponse
  {
    return $this->visitService->index($request, $location);
  }
}
