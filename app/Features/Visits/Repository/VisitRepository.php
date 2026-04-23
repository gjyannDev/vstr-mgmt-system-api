<?php

namespace App\Features\Visits\Repository;

use App\Models\Visit;
use App\Repositories\BaseRepository;

class VisitRepository extends BaseRepository
{
    public function __construct(Visit $model)
    {
        $this->model = $model;
    }

    public function list(array $params = []): array
    {
        $search = $params['search'] ?? null;
        $status = $params['status'] ?? null;
        $visitTypeId = $params['visit_type_id'] ?? null;
        $createdDate = $params['createdDate'] ?? null;
        $filter = is_array($params['filter'] ?? null) ? $params['filter'] : [];

        if (is_string($search) && $search !== '') {
            $filter['purpose'] = ['like', '%' . $search . '%'];
        }

        if ($status !== null) {
            $filter['status'] = $status;
        }

        if ($visitTypeId !== null) {
            $filter['visit_type_id'] = $visitTypeId;
        }

        if (is_string($createdDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $createdDate) === 1) {
            $filter['created_at'] = ['like', $createdDate . '%'];
        }

        return $this->findPaginatedData([
            'filter' => $filter,
            'pageIndex' => $params['pageIndex'] ?? 0,
            'pageSize' => $params['pageSize'] ?? 10,
            'sort' => $params['sort'] ?? ['check_in_at' => 'desc'],
            'with' => $params['with'] ?? ['visitor'],
            'select' => $params['select'] ?? ['*'],
        ]);
    }
}
