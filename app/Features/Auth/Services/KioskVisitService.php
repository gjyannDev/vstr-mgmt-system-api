<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Repository\KioskRepository;
use App\Features\Auth\Requests\StoreKioskVisitRequest;
use App\Models\Kiosk;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class KioskVisitService
{
  use ApiResponse;

  public function __construct(private KioskRepository $kioskRepo) {}

  public function store(StoreKioskVisitRequest $request): JsonResponse
  {
    $identity = $request->user();

    if (! $identity instanceof Kiosk) {
      return $this->errorResponse('Forbidden. Kiosk device token required.', null, 403);
    }

    $data = $request->validated();
    $now = now();

    $visitId = $this->kioskRepo->createVisit([
      'tenant_id' => $identity->tenant_id,
      'location_id' => $identity->location_id,
      'visitor_id' => (int) $data['visitor_id'],
      'host_id' => $data['host_id'] ?? null,
      'visit_type_id' => (int) $data['visit_type_id'],
      'purpose' => $data['purpose'] ?? null,
      'status' => 'checked_in',
      'check_in_at' => $now,
      'notes' => $data['notes'] ?? null,
      'created_at' => $now,
      'updated_at' => $now,
    ]);

    $this->kioskRepo->updateLastSeen($identity);

    $visit = $this->kioskRepo->findByVisitId($visitId);

    return $this->successResponse('Visit created successfully.', [
      'visit' => $visit,
    ], 201);
  }
}
