<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Repository\KioskRepository;
use App\Features\Auth\Requests\StoreKioskRequest;
use App\Models\Kiosk;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

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

        if (! $user || ! $user->hasAssignedLocation($requestedLocationId)) {
            return $this->errorResponse('Admin can only create kiosks in their assigned location(s).', null, 403);
        }

        $kiosk = $this->kioskRepo->createKiosk([
            'tenant_id' => $tenantId,
            'location_id' => $requestedLocationId,
            'name' => $data['name'],
            'status' => $data['status'] ?? Kiosk::STATUS_ACTIVE,
        ]);

        [$activationCode, $codeHash] = $this->makeUniqueActivationCode();

        $activationCodeRecord = $this->kioskRepo->createActivationCode([
            'kiosk_id' => $kiosk->id,
            'code_hash' => $codeHash,
            'expires_at' => now()->addMinutes(10),
        ]);

        return $this->successResponse('Kiosk created successfully.', [
            'kiosk' => $kiosk,
            'activation_code' => $activationCode,
            'activation_expires_at' => $activationCodeRecord->expires_at,
        ], 201);
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
