<?php

namespace App\Features\Visits\Services;

use App\Features\Visits\Repository\VisitRepository;
use App\Models\Kiosk;
use App\Models\Location;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitService
{
  use ApiResponse;

  public function __construct(private VisitRepository $repo) {}

  public function index(Request $request, Location $location): JsonResponse
  {
    $search = $request->query('search');
    $status = $request->query('status');
    $visitTypeId = $request->query('visit_type_id');
    $createdDate = $request->query('createdDate');

    $params = [
      'search' => is_string($search) ? $search : null,
      'status' => $status,
      'visit_type_id' => $visitTypeId,
      'createdDate' => is_string($createdDate) ? $createdDate : null,
      'filter' => [
        'tenant_id' => $request->user()?->tenant_id,
        'location_id' => $location->id,
      ],
      'pageIndex' => (int) $request->query('pageIndex', 0),
      'pageSize' => (int) $request->query('pageSize', 10),
      'sort' => $request->query('sort', ['check_in_at' => 'desc']),
      'with' => $request->query('with', ['visitor']),
      'select' => $request->query('select', ['*']),
    ];

    $rows = $this->repo->list($params);

    $user = $request->user();

    // If kiosk client, we might return a simplified payload in future
    if ($user instanceof Kiosk) {
      return $this->successResponse('Visits fetched successfully.', $rows);
    }

    return $this->successResponse('Visits fetched successfully.', $rows);
  }
}
