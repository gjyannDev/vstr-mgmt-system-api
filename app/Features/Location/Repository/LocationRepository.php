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
        $type = $params['type'] ?? null;
        $state = $params['state'] ?? null;
        $createdDate = $params['createdDate'] ?? null;
        $filter = is_array($params['filter'] ?? null) ? $params['filter'] : [];

        if (is_string($search) && $search !== '') {
            $filter['name'] = ['like', '%' . $search . '%'];
        }

        if (is_string($type) && $type !== '') {
            $filter['type'] = $type;
        }

        if (is_string($state) && $state !== '') {
            $filter['state'] = $state;
        }

        if (is_string($createdDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $createdDate) === 1) {
            $filter['created_at'] = ['like', $createdDate . '%'];
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

    /**
     * Return lightweight list of locations (id + name) scoped by tenant and optional search
     * @param array $params { search?: string }
     * @return array
     */
    public function listSimple(array $params = []): array
    {
        $search = $params['search'] ?? null;

        $query = $this->model->newQuery();

        if (isset($params['tenant_id'])) {
            $query->where('tenant_id', $params['tenant_id']);
        }

        if (is_string($search) && $search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $rows = $query->select(['id', 'name', 'address_line1', 'city', 'state'])->get()->toArray();

        return ['rows' => $rows];
    }
}
