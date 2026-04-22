<?php

namespace App\Features\user\Repository;

use App\Models\Location;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class AdminUserRepository extends BaseRepository
{
  public function __construct(User $model)
  {
    $this->model = $model;
  }

  public function list(array $params = []): array
  {
    $search = $params['search'] ?? null;
    $createdDate = $params['createdDate'] ?? null;
    $tenantId = $params['tenantId'] ?? null;
    $filter = is_array($params['filter'] ?? null) ? $params['filter'] : [];

    $filter['role'] = 'admin';

    if (is_string($tenantId) && $tenantId !== '') {
      $filter['tenant_id'] = $tenantId;
    }

    if (is_string($search) && $search !== '') {
      $searchColumn = str_contains($search, '@') ? 'email' : 'name';
      $filter[$searchColumn] = ['like', '%' . $search . '%'];
    }

    if (is_string($createdDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $createdDate) === 1) {
      $filter['created_at'] = ['like', $createdDate . '%'];
    }

    return $this->findPaginatedData([
      'filter' => $filter,
      'pageIndex' => $params['pageIndex'] ?? 0,
      'pageSize' => $params['pageSize'] ?? 10,
      'sort' => $params['sort'] ?? ['created_at' => 'desc'],
      'with' => $params['with'] ?? ['locations'],
      'select' => $params['select'] ?? ['*'],
    ]);
  }

  public function createAdmin(array $data, array $locationIds): User
  {
    /** @var User $admin */
    $admin = DB::transaction(function () use ($data, $locationIds): User {
      /** @var User $created */
      $created = $this->create($data);

      $created->locations()->sync($locationIds);
      $created->forceFill([
        'location_id' => $locationIds[0] ?? null,
      ])->save();

      return $created;
    });

    /** @var User|null $fresh */
    $fresh = $admin->fresh(['locations']);

    return $fresh instanceof User ? $fresh : $admin->load('locations');
  }

  public function updateAdmin(User $admin, array $data, ?array $locationIds = null): User
  {
    DB::transaction(function () use ($admin, $data, $locationIds): void {
      if ($data !== []) {
        $admin->update($data);
      }

      if ($locationIds !== null) {
        $admin->locations()->sync($locationIds);
        $admin->forceFill([
          'location_id' => $locationIds[0] ?? null,
        ])->save();
      }
    });

    /** @var User|null $fresh */
    $fresh = $admin->fresh(['locations']);

    return $fresh instanceof User ? $fresh : $admin->load('locations');
  }

  public function deleteAdmin(User $admin): bool
  {
    return (bool) DB::transaction(function () use ($admin): bool {
      $admin->locations()->detach();

      return (bool) $admin->delete();
    });
  }

  /**
   * @param  array<int, string>  $locationIds
   * @return array<int, string>
   */
  public function resolveTenantLocationIds(string $tenantId, array $locationIds): array
  {
    if ($locationIds === []) {
      return [];
    }

    $validIds = Location::query()
      ->where('tenant_id', $tenantId)
      ->whereIn('id', $locationIds)
      ->pluck('id')
      ->all();

    $validIdMap = array_fill_keys($validIds, true);
    $orderedIds = [];

    foreach ($locationIds as $locationId) {
      if (isset($validIdMap[$locationId])) {
        $orderedIds[] = $locationId;
      }
    }

    return array_values(array_unique($orderedIds));
  }
}
