<?php

namespace App\Features\Location\Repository;

use App\Models\Location;
use App\Repositories\BaseRepository;

class LocationRepository extends BaseRepository
{
    public function __construct(Location $model)
    {
        $this->model = $model;
    }

    public function list(array $params = []): array
    {
        $search = $params['search'] ?? null;
        $filter = is_array($params['filter'] ?? null) ? $params['filter'] : [];

        if (is_string($search) && $search !== '') {
            $filter['name'] = ['like', '%' . $search . '%'];
        }

        return $this->findPaginatedData([
            'filter' => $filter,
            'pageIndex' => $params['pageIndex'] ?? 0,
            'pageSize' => $params['pageSize'] ?? 10,
            'sort' => $params['sort'] ?? ['created_at' => 'desc'],
            'with' => $params['with'] ?? [],
            'select' => $params['select'] ?? ['*'],
        ]);
    }

    public function createLocation(array $data): Location
    {
        /** @var Location $location */
        $location = $this->create($data);

        return $location;
    }

    public function updateLocation(Location $location, array $data): Location
    {
        $location->update($data);

        /** @var Location $fresh */
        $fresh = $location->fresh();

        return $fresh;
    }

    public function deleteLocation(Location $location): bool
    {
        return (bool) $location->delete();
    }
}
