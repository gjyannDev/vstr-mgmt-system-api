<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Repository\KioskRepository;
use App\Features\Auth\Requests\StoreKioskRequest;
use App\Features\Auth\Requests\IndexKioskRequest;
use App\Features\Auth\Requests\UpdateKioskRequest;
use App\Models\Kiosk;
use App\Models\KioskActivationCode;
use App\Models\VisitType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KioskAdminService
{
  use ApiResponse;

  public function __construct(private KioskRepository $kioskRepo) {}

  public function store(StoreKioskRequest $request): JsonResponse
  {
    $user = $request->user();
    $tenantId = is_string($user?->tenant_id) ? $user->tenant_id : null;

    if ($tenantId === null || $tenantId === '') {
      return $this->errorResponse('Authenticated admin has no tenant assignment.', null, 422);
    }

    $data = $request->validated();
    $requestedLocationId = (string) $data['location_id'];

    // normalize visit type ids (support both singular and array payloads)
    $visitTypeIds = $data['visit_type_ids'] ?? [];
    if (empty($visitTypeIds) && ! empty($data['visit_type_id'])) {
      $visitTypeIds = [$data['visit_type_id']];
    }

    // validate provided visit types belong to the requested location
    foreach ($visitTypeIds as $vtId) {
      $vt = VisitType::find($vtId);
      if (! $vt) {
        return $this->errorResponse('Visit type not found.', null, 422);
      }
      if ((string) $vt->location_id !== $requestedLocationId) {
        return $this->errorResponse('Visit type must belong to the kiosk location.', null, 422);
      }
    }

    if (! $user || ! $user->hasAssignedLocation($requestedLocationId)) {
      return $this->errorResponse('Admin can only create kiosks in their assigned location(s).', null, 403);
    }

    $kioskPayload = [
      'tenant_id' => $tenantId,
      'location_id' => $requestedLocationId,
      'name' => $data['name'],
      'status' => $data['status'] ?? Kiosk::STATUS_ACTIVE,
    ];

    $kiosk = $this->kioskRepo->createKiosk($kioskPayload);

    [$activationCode, $codeHash] = $this->makeUniqueActivationCode();

    $activationCodeRecord = $this->kioskRepo->createActivationCode([
      'kiosk_id' => $kiosk->id,
      'code_hash' => $codeHash,
      'expires_at' => now()->addMinutes(15),
      'created_by' => $user?->id,
      'created_ip' => $request->ip(),
    ]);

    // persist many-to-many visit type associations (if provided)
    if (! empty($visitTypeIds)) {
      $kiosk->visitTypes()->sync($visitTypeIds);

      // keep legacy single column for backwards compatibility set to first id
      $kiosk->forceFill(['visit_type_id' => $visitTypeIds[0]])->save();
    }

    return $this->successResponse('Kiosk created successfully.', [
      'kiosk' => $kiosk,
      'activation_code' => $activationCode,
      'activation_expires_at' => $activationCodeRecord->expires_at,
    ], 201);
  }

  public function index(IndexKioskRequest $request): JsonResponse
  {
    $user = $request->user();
    $tenantId = is_string($user?->tenant_id) ? $user->tenant_id : null;

    if ($tenantId === null || $tenantId === '') {
      return $this->errorResponse('Authenticated admin has no tenant assignment.', null, 422);
    }

    $data = $request->validated();

    $paginated = $this->kioskRepo->paginateForTenant($tenantId, [
      'pageIndex' => $data['pageIndex'] ?? 0,
      'pageSize' => $data['pageSize'] ?? 10,
      'search' => $data['search'] ?? null,
      'location_id' => $data['location_id'] ?? null,
    ]);

    // attach active activation expiry + visit type ids if present
    $rows = $paginated['rows']->map(function ($kiosk) {
      $active = $this->kioskRepo->getActiveActivationCodeForKiosk($kiosk);
      $kiosk->setAttribute('active_code_expires_at', $active ? $active->expires_at : null);

      // if visitTypes relation loaded, expose ids and simple objects
      if ($kiosk->relationLoaded('visitTypes')) {
        $kiosk->setAttribute('visit_type_ids', $kiosk->visitTypes->pluck('id')->toArray());
        $kiosk->setAttribute('visit_types', $kiosk->visitTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toArray());
      }

      return $kiosk;
    })->values();

    return $this->successResponse('Kiosks fetched successfully.', [
      'rows' => $rows,
      'totalCount' => $paginated['totalCount'],
    ]);
  }

  public function show(Request $request, Kiosk $kiosk): JsonResponse
  {
    $user = $request->user();
    $tenantId = is_string($user?->tenant_id) ? $user->tenant_id : null;

    if ($tenantId === null || $tenantId === '') {
      return $this->errorResponse('Authenticated admin has no tenant assignment.', null, 422);
    }

    if (! $user || ! $user->hasAssignedLocation($kiosk->location_id)) {
      return $this->errorResponse('Admin can only view kiosks in their assigned location(s).', null, 403);
    }

    $active = $this->kioskRepo->getActiveActivationCodeForKiosk($kiosk);
    $kiosk->setAttribute('active_code_expires_at', $active ? $active->expires_at : null);

    // load visit types and expose simple arrays for frontend
    $kiosk->loadMissing('visitTypes');
    $kiosk->setAttribute('visit_type_ids', $kiosk->visitTypes->pluck('id')->toArray());
    $kiosk->setAttribute('visit_types', $kiosk->visitTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toArray());

    return $this->successResponse('Kiosk fetched successfully.', ['kiosk' => $kiosk]);
  }

  public function update(UpdateKioskRequest $request, Kiosk $kiosk): JsonResponse
  {
    $user = $request->user();
    $tenantId = is_string($user?->tenant_id) ? $user->tenant_id : null;

    if ($tenantId === null || $tenantId === '') {
      return $this->errorResponse('Authenticated admin has no tenant assignment.', null, 422);
    }

    if (! $user || ! $user->hasAssignedLocation($kiosk->location_id)) {
      return $this->errorResponse('Admin can only update kiosks in their assigned location(s).', null, 403);
    }

    $data = $request->validated();

    if (! empty($data['location_id']) && ! $user->hasAssignedLocation($data['location_id'])) {
      return $this->errorResponse('Admin cannot assign kiosk to an unassigned location.', null, 403);
    }

    // normalize visit type ids (support both singular and array payloads)
    $visitTypeIds = $data['visit_type_ids'] ?? [];
    if (empty($visitTypeIds) && ! empty($data['visit_type_id'])) {
      $visitTypeIds = [$data['visit_type_id']];
    }

    // validate provided visit types belong to the target location if provided
    $targetLocation = $data['location_id'] ?? $kiosk->location_id;
    foreach ($visitTypeIds as $vtId) {
      $vt = VisitType::find($vtId);
      if (! $vt) {
        return $this->errorResponse('Visit type not found.', null, 422);
      }
      if ((string) $vt->location_id !== (string) $targetLocation) {
        return $this->errorResponse('Visit type must belong to the kiosk location.', null, 422);
      }
    }

    $updateData = [];
    if (array_key_exists('name', $data)) {
      $updateData['name'] = $data['name'];
    }
    if (array_key_exists('status', $data)) {
      $updateData['status'] = $data['status'];
    }
    if (array_key_exists('location_id', $data)) {
      $updateData['location_id'] = $data['location_id'];
    }
    if (array_key_exists('visit_type_id', $data)) {
      $updateData['visit_type_id'] = $data['visit_type_id'];
    }

    $updated = $this->kioskRepo->update($kiosk->id, $updateData);

    // persist many-to-many visit type associations if provided
    if (! empty($visitTypeIds)) {
      $updated->visitTypes()->sync($visitTypeIds);

      // keep legacy single column for backwards compatibility set to first id
      $updated->forceFill(['visit_type_id' => $visitTypeIds[0]])->save();
    }

    // refresh visit types attributes for response
    $updated->loadMissing('visitTypes');
    $updated->setAttribute('visit_type_ids', $updated->visitTypes->pluck('id')->toArray());
    $updated->setAttribute('visit_types', $updated->visitTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toArray());

    return $this->successResponse('Kiosk updated successfully.', ['kiosk' => $updated]);
  }

  public function regenerate(Request $request, Kiosk $kiosk): JsonResponse
  {
    $user = $request->user();
    $tenantId = is_string($user?->tenant_id) ? $user->tenant_id : null;

    if ($tenantId === null || $tenantId === '') {
      return $this->errorResponse('Authenticated admin has no tenant assignment.', null, 422);
    }

    if (! $user || ! $user->hasAssignedLocation($kiosk->location_id)) {
      return $this->errorResponse('Admin can only regenerate codes for kiosks in their assigned location(s).', null, 403);
    }

    $result = DB::transaction(function () use ($kiosk, $user, $request) {
      // expire previous codes
      $this->kioskRepo->expireAllActivationCodes($kiosk);

      [$activationCode, $codeHash] = $this->makeUniqueActivationCode();

      $activationCodeRecord = $this->kioskRepo->createActivationCode([
        'kiosk_id' => $kiosk->id,
        'code_hash' => $codeHash,
        'expires_at' => now()->addMinutes(15),
        'created_by' => $user?->id,
        'created_ip' => $request->ip(),
      ]);

      return [
        'activation_code' => $activationCode,
        'activation_expires_at' => $activationCodeRecord->expires_at,
      ];
    });

    if (! $result) {
      return $this->errorResponse('Failed to regenerate activation code.', null, 500);
    }

    return $this->successResponse('Activation code regenerated successfully.', $result);
  }

  public function revokeTokens(Request $request, Kiosk $kiosk): JsonResponse
  {
    $user = $request->user();
    $tenantId = is_string($user?->tenant_id) ? $user->tenant_id : null;

    if ($tenantId === null || $tenantId === '') {
      return $this->errorResponse('Authenticated admin has no tenant assignment.', null, 422);
    }

    if (! $user || ! $user->hasAssignedLocation($kiosk->location_id)) {
      return $this->errorResponse('Admin can only revoke tokens for kiosks in their assigned location(s).', null, 403);
    }

    $this->kioskRepo->deleteAllTokens($kiosk);

    return $this->successResponse('Kiosk tokens revoked successfully.');
  }

  private function makeUniqueActivationCode(): array
  {
    for ($attempt = 0; $attempt < 5; $attempt++) {
      $code = $this->generateActivationCode();
      $hash = $this->hashActivationCode($code);

      $exists = $this->kioskRepo->existsByActivationCodeHash($hash);
      if (! $exists) {
        return [$code, $hash];
      }
    }

    abort(500, 'Failed to generate a unique activation code.');
  }

  private function generateActivationCode(int $length = 8): string
  {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max = strlen($alphabet) - 1;
    $code = '';

    for ($i = 0; $i < $length; $i++) {
      $code .= $alphabet[random_int(0, $max)];
    }

    return $code;
  }

  private function hashActivationCode(string $code): string
  {
    $key = (string) config('app.key', 'kiosk-activation-fallback');

    return hash_hmac('sha256', strtoupper(trim($code)), $key);
  }
}
