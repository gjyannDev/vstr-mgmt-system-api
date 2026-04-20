<?php

namespace App\Features\Auth\Repository;

use App\Models\Kiosk;
use App\Models\KioskActivationCode;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class KioskRepository extends BaseRepository
{
    public function __construct(Kiosk $model)
    {
        $this->model = $model;
    }

    public function createKiosk(array $data): Kiosk
    {
        /** @var Kiosk $kiosk */
        $kiosk = $this->create($data);

        return $kiosk;
    }

    public function createActivationCode(array $data): KioskActivationCode
    {
        /** @var KioskActivationCode $code */
        $code = KioskActivationCode::query()->create($data);

        return $code;
    }

    public function existsByActivationCodeHash(string $codeHash): bool
    {
        return KioskActivationCode::query()->where('code_hash', $codeHash)->exists();
    }

    public function findByValidActivationCodeHash(string $codeHash): ?KioskActivationCode
    {
        /** @var KioskActivationCode|null $code */
        $code = KioskActivationCode::query()
            ->with('kiosk')
            ->where('code_hash', $codeHash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        return $code;
    }

    public function markActivationCodeAsUsed(int $activationCodeId): bool
    {
        $updated = KioskActivationCode::query()
            ->whereKey($activationCodeId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['used_at' => now()]);

        return $updated === 1;
    }

    public function createVisit(array $data): int
    {
        return (int) DB::table('visits')->insertGetId($data);
    }

    public function findByVisitId(int $visitId): ?object
    {
        return DB::table('visits')->where('id', $visitId)->first();
    }

    public function deleteAllTokens(Kiosk $kiosk): void
    {
        $kiosk->tokens()->delete();
    }

    public function createToken(Kiosk $kiosk, string $tokenName, array $abilities = ['*']): string
    {
        return $kiosk->createToken($tokenName, $abilities)->plainTextToken;
    }

    public function deleteCurrentAccessToken(Kiosk $kiosk): void
    {
        $token = $kiosk->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function updateLastSeen(Kiosk $kiosk): Kiosk
    {
        $kiosk->forceFill(['last_seen_at' => now()])->save();

        return $kiosk->fresh();
    }
}
