<?php

namespace App\Features\Auth\Services;

use App\Features\Auth\Repository\KioskRepository;
use App\Features\Auth\Requests\ActivateKioskRequest;
use App\Models\Kiosk;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KioskAuthService
{
    use ApiResponse;

    public function __construct(private KioskRepository $kioskRepo) {}

    public function activate(ActivateKioskRequest $request): JsonResponse
    {
        $data = $request->validated();

        $code = strtoupper(trim((string) $data['code']));
        $codeHash = $this->hashActivationCode($code);

        $activationCode = $this->kioskRepo->findByValidActivationCodeHash($codeHash);

        if (! $activationCode || ! $activationCode->kiosk) {
            return $this->errorResponse('Invalid or expired activation code.', null, 422);
        }

        $kiosk = $activationCode->kiosk;

        if (! $kiosk->isActive()) {
            return $this->errorResponse('Kiosk device is disabled.', null, 403);
        }

        $token = DB::transaction(function () use ($activationCode, $kiosk): ?string {
            $marked = $this->kioskRepo->markActivationCodeAsUsed($activationCode->id);

            if (! $marked) {
                return null;
            }

            $this->kioskRepo->deleteAllTokens($kiosk);
            $this->kioskRepo->updateLastSeen($kiosk);

            return $this->kioskRepo->createToken($kiosk, 'kiosk-' . $kiosk->id . '-token', ['kiosk']);
        });

        if (! $token) {
            return $this->errorResponse('Invalid or expired activation code.', null, 422);
        }

        return $this->successResponse('Kiosk activated successfully.', [
            'kiosk' => $kiosk->fresh(),
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $identity = $request->user();

        if (! $identity instanceof Kiosk) {
            return $this->errorResponse('Forbidden. Kiosk device token required.', null, 403);
        }

        $kiosk = $this->kioskRepo->updateLastSeen($identity);

        return $this->successResponse('Kiosk profile fetched successfully.', [
            'kiosk' => $kiosk,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $identity = $request->user();

        if (! $identity instanceof Kiosk) {
            return $this->errorResponse('Forbidden. Kiosk device token required.', null, 403);
        }

        $this->kioskRepo->deleteCurrentAccessToken($identity);

        return $this->successResponse('Kiosk logged out successfully.');
    }

    private function hashActivationCode(string $code): string
    {
        $key = (string) config('app.key', 'kiosk-activation-fallback');

        return hash_hmac('sha256', strtoupper(trim($code)), $key);
    }
}
