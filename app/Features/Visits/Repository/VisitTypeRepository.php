<?php

namespace App\Features\Visits\Repository;

use App\Models\VisitType;
use App\Repositories\BaseRepository;

class VisitTypeRepository extends BaseRepository
{
    public function __construct(VisitType $model)
    {
        $this->model = $model;
    }

    public function list(array $params = []): array
    {
        $search = $params['search'] ?? null;
        $requiresApproval = $params['requires_approval'] ?? null;
        $active = $params['active'] ?? null;
        $createdDate = $params['createdDate'] ?? null;
        $filter = is_array($params['filter'] ?? null) ? $params['filter'] : [];

        if (is_string($search) && $search !== '') {
            $filter['name'] = ['like', '%' . $search . '%'];
        }

        if ($requiresApproval !== null) {
            $filter['requires_approval'] = (bool) $requiresApproval;
        }

        if ($active !== null) {
            $filter['active'] = (bool) $active;
        }

        if (is_string($createdDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $createdDate) === 1) {
            $filter['created_at'] = ['like', $createdDate . '%'];
        }

        return $this->findPaginatedData([
            'filter' => $filter,
            'pageIndex' => $params['pageIndex'] ?? 0,
            'pageSize' => $params['pageSize'] ?? 10,
            'sort' => $params['sort'] ?? ['created_at' => 'desc'],
            'with' => $params['with'] ?? ['formFields'],
            'select' => $params['select'] ?? ['*'],
        ]);
    }

    public function createVisitType(array $data): VisitType
    {
        /** @var VisitType $visitType */
        $visitType = $this->create($data);

        return $visitType;
    }

    public function updateVisitType(VisitType $visitType, array $data): VisitType
    {
        $visitType->update($data);

        /** @var VisitType $fresh */
        $fresh = $visitType->fresh();

        return $fresh;
    }

    public function deleteVisitType(VisitType $visitType): bool
    {
        return (bool) $visitType->delete();
    }
}
