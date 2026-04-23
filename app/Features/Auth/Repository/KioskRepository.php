<?php

namespace App\Features\Auth\Repository;

use App\Models\Kiosk;
use App\Models\KioskActivationCode;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

  public function markActivationCodeAsUsed(string $activationCodeId): bool
  {
    $updated = KioskActivationCode::query()
      ->whereKey($activationCodeId)
      ->whereNull('used_at')
      ->where('expires_at', '>', now())
      ->update(['used_at' => now()]);

    return $updated === 1;
  }

  public function createVisit(array $data): string
  {
    $visitId = (string) Str::uuid();
    $data['id'] = $visitId;

    DB::table('visits')->insert($data);

    return $visitId;
  }

  public function findByVisitId(string $visitId): ?object
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

  public function paginateForTenant(string $tenantId, array $params = []): array
  {
    $pageIndex = max(0, (int) ($params['pageIndex'] ?? 0));
    $pageSize = max(1, (int) ($params['pageSize'] ?? 10));

    $filter = $params['filter'] ?? [];
    // always scope by tenant
    $filter['tenant_id'] = $tenantId;

    if (! empty($params['location_id'])) {
      $filter['location_id'] = $params['location_id'];
    }

    if (! empty($params['search'])) {
      $filter['name'] = ['like', '%' . $params['search'] . '%'];
    }

    $with = [
      'activationCodes' => function ($q) {
        $q->whereNull('used_at')->where('expires_at', '>', now());
      },
      // include visit types for admin UIs so we can expose ids/names
      'visitTypes' => function ($q) {
        $q->select('visit_types.id', 'visit_types.name');
      },
    ];

    return $this->findPaginatedData([
      'filter' => $filter,
      'pageIndex' => $pageIndex,
      'pageSize' => $pageSize,
      'sort' => $params['sort'] ?? ['created_at' => 'desc'],
      'with' => $with,
    ]);
  }

  public function getActiveActivationCodeForKiosk(Kiosk $kiosk): ?KioskActivationCode
  {
    return KioskActivationCode::query()
      ->where('kiosk_id', $kiosk->id)
      ->whereNull('used_at')
      ->where('expires_at', '>', now())
      ->orderBy('created_at', 'desc')
      ->first();
  }

  public function expireAllActivationCodes(Kiosk $kiosk): void
  {
    KioskActivationCode::query()
      ->where('kiosk_id', $kiosk->id)
      ->whereNull('used_at')
      ->update(['used_at' => now()]);
  }
}
